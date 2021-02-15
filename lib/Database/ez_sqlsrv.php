<?php

declare(strict_types=1);

namespace ezsql\Database;

use Exception;
use ezsql\ezsqlModel;
use ezsql\ConfigInterface;
use ezsql\DatabaseInterface;
use function ezsql\functions\setInstance;

class ez_sqlsrv extends ezsqlModel implements DatabaseInterface
{
    private $return_val = 0;
    private $is_insert = false;
    private $shortcutUsed = false;
    private $isTransactional = false;

    /**
     * ezSQL non duplicating data type id's; converting type ids to str
     */
    private $ezsql_sqlsrv_type2str_non_dup = array(
        -5 => 'bigint', -7 => 'bit', 1 => 'char', 91 => 'date', -155 => 'datetimeoffset', 6 => 'float', -4 => 'image', 4 => 'int', -8 => 'nchar',
        -10 => 'ntext', 2 => 'numeric', -9 => 'nvarchar', 7 => 'real', 5 => 'smallint', -1 => 'text', -154 => 'time', -6 => 'tinyint', -151 => 'udt',
        -11 => 'uniqueidentifier', -3 => 'varbinary', 12 => 'varchar', -152 => 'xml',
    );

    /**
     * Database connection handle
     * @var resource
     */
    private $dbh;

    /**
     * Query result
     * @var mixed
     */
    private $result;

    /**
     * Database configuration setting
     * @var ConfigInterface
     */
    private $database;

    public function __construct(ConfigInterface $settings = null)
    {
        if (empty($settings)) {
            throw new Exception(\MISSING_CONFIGURATION);
        }

        parent::__construct();
        $this->database = $settings;

        if (empty($GLOBALS['ez' . \SQLSRV]))
            $GLOBALS['ez' . \SQLSRV] = $this;
        setInstance($this);
    }

    public function settings()
    {
        return $this->database;
    }

    /**
     *  Short hand way to connect to sqlsrv database server
     *  and select a sqlsrv database at the same time
     */
    public function quick_connect($user = '', $password = '', $name = '', $host = 'localhost')
    {
        $return_val = false;
        $this->_connected = false;
        if (!$this->connect($user, $password, $name, $host));
        else {
            $return_val = true;
            $this->_connected = true;
        }
        return $return_val;
    }

    /**
     *  Try to connect to sqlsrv database server
     */
    public function connect($user = '', $password = '', $name = '', $host = 'localhost')
    {
        $this->_connected = false;

        $user = empty($user) ? $this->database->getUser() : $user;
        $password = empty($password) ? $this->database->getPassword() : $password;
        $name = empty($name) ? $this->database->getName() : $name;
        $host = ($host != 'localhost') ? $this->database->getHost() : $host;

        // Blank user assumes Windows Authentication
        if ($this->isSecure) {
            $connectionOptions = array(
                "UID" => $user,
                "PWD" => $password,
                "Database" => $name,
                "ReturnDatesAsStrings" => true,
                "Encrypt" => true,
                "TrustServerCertificate" => true,
            );
        } else {
            $connectionOptions = array(
                "UID" => $user,
                "PWD" => $password,
                "Database" => $name,
                "ReturnDatesAsStrings" => true,
            );
        }

        // Try to establish the server database handle
        if (($this->dbh = @\sqlsrv_connect($host, $connectionOptions)) === false) {
            $this->register_error(\FAILED_CONNECTION . ' in ' . __FILE__ . ' on line ' . __LINE__);
        } else {
            $this->_connected = true;
            $this->connQueries = 0;
        }

        return $this->_connected;
    }

    /**
     *  Return sqlsrv specific system date syntax
     *  i.e. Oracle: SYSDATE sqlsrv: NOW(), MS-SQL : getDate()
     *
     *  The SQLSRV drivers pull back the data into a Date class.  Converted
     *   it to a string inside of SQL in order to prevent this from occurring
     *  ** make sure to use " AS <label>" after calling this...
     */
    public function sysDate()
    {
        return "GETDATE()";
    }

    /**
     * Creates a prepared query, binds the given parameters and returns the result of the executed
     *
     * @param string $query
     * @param array $param
     * @return bool|mixed
     */
    public function query_prepared(string $query, array $param = null)
    {
        $result = @\sqlsrv_query($this->dbh, $query, $param);
        if ($this->shortcutUsed)
            return $result;

        $this->return_val = 0;
        return $this->processQueryResult($query, $result);
    }

    /**
     * Perform post processing on SQL query call
     *
     * @param string $query
     * @param mixed $result
     * @return bool|void
     */
    private function processQueryResult(string $query, $result = null)
    {
        $this->shortcutUsed = false;

        if (!empty($result))
            $this->result = $result;

        // If there is an error then take note of it..
        if ($this->result === false) {
            $errors = \sqlsrv_errors();
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $sqlError = "ErrorCode: " . $error['code'] . " ### State: " . $error['SQLSTATE'] . " ### Error Message: " . $error['message'] . " ### Query: " . $query;
                    $this->register_error($sqlError);
                }
            }

            return false;
        }

        // Query was an insert, delete, update, replace
        $this->is_insert = false;
        try {
            if (\preg_match("/^(insert|delete|update|replace)\s+/i", $query)) {
                $this->is_insert = true;
                $this->_affectedRows = @\sqlsrv_rows_affected($this->result);

                // Take note of the insert_id
                if (\preg_match("/^(insert|replace)\s+/i", $query)) {
                    $identityResultset = @\sqlsrv_query($this->dbh, "select SCOPE_IDENTITY()");

                    if ($identityResultset != false) {
                        $identityRow = @\sqlsrv_fetch($identityResultset);
                        $this->insertId = $identityRow[0];
                    }
                }
                // Return number of rows affected
                $this->return_val = $this->_affectedRows;
            } else { // Query was a select
                // Take note of column info
                $i = 0;
                foreach (@\sqlsrv_field_metadata($this->result) as $field) {
                    $col = [];
                    foreach ($field as $name => $value) {
                        $name = \strtolower($name);
                        if ($name == "size") {
                            $name = "max_length";
                        } elseif ($name == "type") {
                            $name = "typeid";
                        }

                        //DEFINED FOR E_STRICT
                        $col = new \stdClass();
                        $col->{$name} = $value;
                    }

                    $col->type = $this->get_datatype($col);
                    $this->colInfo[$i++] = $col;
                    unset($col);
                }

                // Store Query Results
                $num_rows = 0;

                while ($row = @\sqlsrv_fetch_object($this->result)) {

                    // Store results as an objects within main array
                    $this->lastResult[$num_rows] = $row;
                    $num_rows++;
                }

                @\sqlsrv_free_stmt($this->result);

                // Log number of rows the query returned
                $this->numRows = $num_rows;

                // Return number of rows selected
                $this->return_val = $this->numRows;
            }
        } catch (\Throwable $ex) {
            return false;
        }

        return $this->return_val;
    }

    /**
     * Perform sqlsrv query and try to determine result value
     *
     * @param string
     * @param bool
     * @return bool|mixed
     */
    public function query(string $query, bool $use_prepare = false)
    {
        $param = [];
        if ($use_prepare) {
            $param = &$this->prepareValues();
        }

        // check for ezQuery placeholder tag and replace tags with proper prepare tag
        $query = \str_replace(\_TAG, '?', $query);

        // if flag to convert query from MySql syntax to MS-Sql syntax is true
        // convert the query
        if ($this->database->getToMssql()) {
            $query = $this->convert($query);
        }

        // Initialize return
        $this->return_val = 0;

        // Flush cached values..
        $this->flush();

        // For reg expressions
        $query = \trim($query);

        // Log how the function was called
        $this->log_query("\$db->query(\"$query\")");

        // Keep track of the last query for debug..
        $this->lastQuery = $query;

        // Count how many queries there have been
        $this->count(true, true);

        // Use core file cache function
        if ($cache = $this->get_cache($query)) {
            return $cache;
        }

        // If there is no existing database connection then try to connect
        if (!isset($this->dbh) || !$this->dbh) {
            $this->connect(
                $this->database->getUser(),
                $this->database->getPassword(),
                $this->database->getName(),
                $this->database->getHost()
            );
        }

        // Perform the query via std sqlsrv_query function..
        if (!empty($param) && \is_array($param) && $this->isPrepareOn()) {
            $this->shortcutUsed = true;
            $this->result = $this->query_prepared($query, $param);
        } else {
            try {
                $this->result = @\sqlsrv_query($this->dbh, $query);
            } catch (\Throwable $ex) {
                //
            }
        }

        if ($this->processQueryResult($query) === false) {
            if ($this->isTransactional)
                throw new \Exception($this->getLastError());

            return false;
        }

        // disk caching of queries
        $this->store_cache($query, $this->is_insert);

        // If debug ALL queries
        $this->trace || $this->debugAll ? $this->debug() : null;

        return $this->return_val;
    }

    /**
     * Convert a Query From MySql Syntax to MS-Sql syntax
     * Following conversions are made:-
     * 1. The '`' character used for MySql queries is not supported - the character is removed.
     * 2. FROM_UNIXTIME method is not supported. The Function is removed.It is replaced with
     *      getDate(). Warning: This logic may not be right.
     * 3. unix_timestamp function is removed.
     * 4. LIMIT keyword is replaced with TOP keyword. Warning: Logic not fully tested.
     * Note: This method is only a small attempt to convert the syntax. There are many aspects which are not covered here.
     *        This method doesn't at all guarantee complete conversion. Certain queries will still
     *        not work. e.g. MS SQL requires all columns in Select Clause to be present in 'group by' clause.
     *        There is no such restriction in MySql.
     */
    public function convert($query)
    {
        //replace the '`' character used for MySql queries, but not
        //supported in MS-Sql
        $query = \str_replace("`", "", $query);

        $limit_str = "/LIMIT[^\w]{1,}([0-9]{1,})([\,]{0,})([0-9]{0,})/i";

        $patterns = array(
            //replace From UnixTime command in MS-Sql, doesn't work
            0 => "/FROM_UNIXTIME\(([^\/]{0,})\)/i",
            //replace unix_timestamp function. Doesn't work in MS-Sql
            1 => "/unix_timestamp\(([^\/]{0,})\)/i",
            //replace LIMIT keyword. Works only on MySql not on MS-Sql with TOP keyword
            2 => $limit_str,
        );

        $replacements = array(
            0 => "getdate()",
            1 => "\\1",
            2 => "",
        );

        $regs = null;
        \preg_match($limit_str, $query, $regs);
        $query = \preg_replace($patterns, $replacements, $query);

        if (isset($regs[2])) {
            $query = \str_ireplace("SELECT ", "SELECT TOP " . $regs[3] . " ", $query);
        } else if (isset($regs[1])) {
            $query = \str_ireplace("SELECT ", "SELECT TOP " . $regs[1] . " ", $query);
        }

        return $query;
    }

    public function get_datatype($col)
    {
        $datatype = "dt not defined";
        if (isset($col->typeid)) {
            switch ($col->typeid) {
                case -2:
                    if ($col->max_length < 8000) {
                        $datatype = \BINARY;
                    } else {
                        $datatype = \TIMESTAMP;
                    }

                    break;
                case 3:
                    if (($col->scale == 4) && ($col->precision == 19)) {
                        $datatype = \MONEY;
                    } else if (($col->scale == 4) && ($col->precision == 10)) {
                        $datatype = \SMALLMONEY;
                    } else {
                        $datatype = \DECIMAL;
                    }

                    break;
                case 93:
                    if (($col->precision == 16) && ($col->scale == 0)) {
                        $datatype = \SMALLDATETIME;
                    } else if (($col->precision == 23) && ($col->scale == 3)) {
                        $datatype = \DATETIME;
                    } else {
                        $datatype = \DATETIME2;
                    }

                    break;
                default:
                    $datatype = $this->ezsql_sqlsrv_type2str_non_dup[$col->typeid];
                    break;
            }
        }

        return $datatype;
    }

    /**
     *  Close the active SQLSRV connection
     */
    public function disconnect()
    {
        $this->connQueries = 0;
        @\sqlsrv_close($this->dbh);
        $this->_connected = false;
    }

    /**
     * Reset database handle
     */
    public function reset()
    {
        $this->dbh = null;
    }

    /**
     * Get connection handle
     */
    public function handle()
    {
        return $this->dbh;
    }

    /**
     * Begin sqlsrv Transaction
     */
    public function beginTransaction()
    {
        @\sqlsrv_begin_transaction($this->dbh);
        $this->isTransactional = true;
    }

    public function commit()
    {
        @\sqlsrv_commit($this->dbh);
        $this->isTransactional = false;
    }

    public function rollback()
    {
        @\sqlsrv_rollback($this->dbh);
        $this->isTransactional = false;
    }
} // end class
