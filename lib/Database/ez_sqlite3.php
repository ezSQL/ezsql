<?php
declare (strict_types = 1);

namespace ezsql\Database;

use Exception;
use ezsql\Configuration;
use ezsql\ezsqlModel;

final class ez_sqlite3 extends ezsqlModel
{
    /**
     * ezSQL error strings - SQLite
     */
    private $ezsql_sqlite3_str = array
        (
        1 => 'Require $path and $name to open an SQLite database',
    );

    private $rows_affected = false;

    protected $preparedValues = array();

    /**
     * Database configuration setting
     * @var Configuration instance
     */
    private $database;

    /**
     *  Constructor - allow the user to perform a quick connect at the
     *  same time as initializing the ez_sqlite3 class
     */
    public function __construct(Configuration $settings)
    {
        if ( ! \class_exists ('ezsqlModel') ) {
            throw new Exception(\CONFIGURATION_REQUIRES);
        }
        
        if (empty($settings) || (!$settings instanceof Configuration)) {
            throw new Exception(\MISSING_CONFIGURATION);
        }
        
        parent::__construct();
        $this->database = $settings;

        // Turn on track errors
        ini_set('track_errors', 1);
        $path = $this->database->getPath();
        $name = $this->database->getName();
        if (!empty($path) && !empty($name)) {
            $this->connect($path, $name);
        }

        $GLOBALS['db_'.\SQLITE] = $this;
        $GLOBALS['db_'.\SQLITE3] = $this;
        \setInstance($this);
    }

    /**
     *  Try to connect to SQLite database server
     */
    public function connect($path = '', $name = '')
    {
        $return_val = false;
        $this->_connected = false;

        // Must have a user and a password
        if (!$path || !$name) {
            $this->register_error($this->ezsql_sqlite3_str[1] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? \trigger_error($this->ezsql_sqlite3_str[1], \E_USER_WARNING) : null;
            // Try to establish the server database handle
        } elseif (!$this->dbh = @new \SQLite3($path . $name)) {
            $this->register_error($php_errormsg);
            $this->show_errors ? \trigger_error($php_errormsg, \E_USER_WARNING) : null;
        } else {
            $return_val = true;
            $this->conn_queries = 0;
            $this->_connected = true;
        }

        return $return_val;
    }

    /**
     *  In the case of SQLite quick_connect is not really needed
     *  because std. connect already does what quick connect does -
     *  but for the sake of consistency it has been included
     */
    public function quick_connect($path = '', $name = '')
    {
        return $this->connect($path, $name);
    }

    /**
     *  Format a SQLite string correctly for safe SQLite insert
     *  (no mater if magic quotes are on or not)
     * @param string $str
     * @return string
     */
    public function escape(string $str)
    {
        return $this->dbh->escapeString(\stripslashes(\preg_replace("/[\r\n]/", '', $str)));
    }

    /**
     *  Return SQLite specific system date syntax
     *  i.e. Oracle: SYSDATE Mysql: NOW()
     */
    public function sysDate()
    {
        return 'now';
    }

    // Get the data type of the value to bind.
    public function getArgType($arg)
    {
        switch (\gettype($arg)) {
            case 'double':return \SQLITE3_FLOAT;
            case 'integer':return \SQLITE3_INTEGER;
            case 'boolean':return \SQLITE3_INTEGER;
            case 'NULL':return \SQLITE3_NULL;
            case 'string':return \SQLITE3_TEXT;
            case 'string':return \SQLITE3_TEXT;
            default:
                $type_error = 'Argument is of invalid type ' . \gettype($arg);
                $this->register_error($type_error);
                $this->show_errors ? \trigger_error($type_error, \E_USER_WARNING) : null;
                return false;
        }
    }

    /**
     * Creates a prepared query, binds the given parameters and returns the result of the executed
     * @param string $query
     * @param array $param
     * @return bool \SQLite3Result
     */
    public function query_prepared($query, $param = null)
    {
        $stmt = $this->dbh->prepare($query);
        foreach ($param as $index => $val) {
            // indexing start from 1 in Sqlite3 statement
            if (\is_array($val)) {
                $ok = $stmt->bindParam($index + 1, $val);
            } else {
                $ok = $stmt->bindValue($index + 1, $val, $this->getArgType($val));
            }

            if (!$ok) {
                $type_error = "Unable to bind param: $val";
                $this->register_error($type_error);
                $this->show_errors ? \trigger_error($type_error, \E_USER_WARNING) : null;
                return false;
            }
        }

        return $stmt->execute();
    }

    /**
     * Perform SQLite query and try to determine result value
     * Basic Query    - see docs for more detail
     * @param string
     * @param bool
     * @return object
     */
    public function query(string $query, $use_prepare = false)
    {
        $param = [];
        if ($use_prepare) {
            $param = $this->prepareValues();
        }

        // check for ezQuery placeholder tag and replace tags with proper prepare tag
        $query = \str_replace(\_TAG, '?', $query);

        // For reg expressions
        $query = \str_replace("/[\n\r]/", '', \trim($query));

        // initialize return
        $return_val = 0;

        // Flush cached values..
        $this->flush();

        // Log how the function was called
        $this->func_call = "\$db->query(\"$query\")";

        // Keep track of the last query for debug..
        $this->last_query = $query;

        // Perform the query via std SQLite3 query or SQLite3 prepare function..
        if (!empty($param) && \is_array($param) && ($this->isPrepareActive())) {
            $this->result = $this->query_prepared($query, $param);
        } else {
            $this->result = $this->dbh->query($query);
        }

        $this->count(true, true);

        // If there is an error then take note of it..
        if (@$this->dbh->lastErrorCode()) {
            $err_str = $this->dbh->lastErrorMsg();
            $this->register_error($err_str);
            $this->show_errors ? \trigger_error($err_str, \E_USER_WARNING) : null;
            return false;
        }

        // Query was an insert, delete, update, replace
        if (\preg_match("/^(insert|delete|update|replace)\s+/i", $query)) {
            $this->rows_affected = @$this->dbh->changes();

            // Take note of the insert_id
            if (\preg_match("/^(insert|replace)\s+/i", $query)) {
                $this->insert_id = @$this->dbh->lastInsertRowID();
            }

            // Return number of rows affected
            $return_val = $this->rows_affected;

            // Query was an select
        } else {
            // Take note of column info
            $i = 0;
            $this->col_info = array();
            while ($i < @$this->result->numColumns()) {
                $this->col_info[$i] = new StdClass;
                $this->col_info[$i]->name = $this->result->columnName($i);
                $this->col_info[$i]->type = null;
                $this->col_info[$i]->max_length = null;
                $i++;
            }

            // Store Query Results
            $num_rows = 0;
            while ($row = @$this->result->fetchArray(\SQLITE3_ASSOC)) {
                // Store result as an objects within main array
                $obj = (object) $row; //convert to object
                $this->last_result[$num_rows] = $obj;
                $num_rows++;
            }

            // Log number of rows the query returned
            $this->num_rows = $num_rows;

            // Return number of rows selected
            $return_val = $this->num_rows;
        }

        if (($param) && \is_array($param) && ($this->isPrepareActive())) {
            $this->result->finalize();
        }

        // If debug ALL queries
        $this->trace || $this->debug_all ? $this->debug() : null;

        return $return_val;
    }
}