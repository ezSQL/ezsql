<?php

declare(strict_types=1);

namespace ezsql\Database;

use Exception;
use ezsql\ezsqlModel;
use ezsql\ConfigInterface;
use ezsql\DatabaseInterface;
use function ezsql\functions\setInstance;

class ez_sqlite3 extends ezsqlModel implements DatabaseInterface
{
    protected $is_insert = false;
    private $return_val = 0;
    private $shortcutUsed = false;
    private $isTransactional = false;

    /**
     * Database connection handle
     * @var \SQLite3
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

    /**
     *  Constructor - allow the user to perform a quick connect at the
     *  same time as initializing the ez_sqlite3 class
     */
    public function __construct(ConfigInterface $settings = null)
    {
        if (empty($settings)) {
            throw new Exception(\MISSING_CONFIGURATION);
        }

        parent::__construct();
        $this->database = $settings;

        // Turn on track errors
        ini_set('track_errors', '1');

        if (!isset($GLOBALS['ez' . \SQLITE3]))
            $GLOBALS['ez' . \SQLITE3] = $this;
        setInstance($this);
    }

    public function settings()
    {
        return $this->database;
    }

    /**
     *  Try to connect to SQLite database server
     */
    public function connect($path = '', $name = '')
    {
        $this->_connected = false;

        $path = empty($path) ? $this->database->getPath() : $path;
        $name = empty($name) ? $this->database->getName() : $name;

        // Try to establish the server database handle
        if (!$this->dbh = @new \SQLite3($path . $name)) {
            //$this->register_error(\FAILED_CONNECTION);
            //$this->show_errors ? \trigger_error(\FAILED_CONNECTION, \E_USER_WARNING) : null;
        } else {
            $this->connQueries = 0;
            $this->_connected = true;
        }

        return $this->_connected;
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
            case 'double':
                return \SQLITE3_FLOAT;
            case 'integer':
                return \SQLITE3_INTEGER;
            case 'boolean':
                return \SQLITE3_INTEGER;
            case 'NULL':
                return \SQLITE3_NULL;
            case 'string':
                return \SQLITE3_TEXT;
            case 'string':
                return \SQLITE3_TEXT;
            default:
                $type_error = 'Argument is of invalid type ' . \gettype($arg);
                return $this->register_error($type_error);
        }
    }

    /**
     * Creates a prepared query, binds the given parameters and returns the result of the executed
     *
     * @param string $query
     * @param array $param
     * @return bool \SQLite3Result
     */
    public function query_prepared(string $query, array $param = null)
    {
        $stmt = $this->dbh->prepare($query);
        if (!$stmt instanceof \SQLite3Stmt) {
            if ($this->isTransactional)
                throw new \Exception($this->getLastError());

            return false;
        }

        foreach ($param as $index => $val) {
            // indexing start from 1 in Sqlite3 statement
            if (\is_array($val)) {
                $ok = $stmt->bindParam($index + 1, $val);
            } else {
                $ok = $stmt->bindValue($index + 1, $val, $this->getArgType($val));
            }

            if (!$ok) {
                $type_error = "Unable to bind param: $val";
                return $this->register_error($type_error);
            }
        }

        $result = $stmt->execute();
        if ($this->shortcutUsed)
            return $result;

        $this->processQueryResult($query, $result);
        if ((\strpos($query, 'SELECT ') !== false) || (\strpos($query, 'select ') !== false))
            $this->result->finalize();

        return $this->return_val;
    }

    /**
     * Perform post processing on SQL query call
     *
     * @param string $query
     * @param mixed $result
     * @param array $param
     * @return bool|void
     */
    private function processQueryResult(string $query, $result = null)
    {
        $this->shortcutUsed = false;

        if (!empty($result))
            $this->result = $result;

        $this->count(true, true);

        // If there is an error then take note of it..
        if (@$this->dbh->lastErrorCode()) {
            $err_str = $this->dbh->lastErrorMsg();
            return $this->register_error($err_str);
        }

        // Query was an insert, delete, update, replace
        if (\preg_match("/^(insert|delete|update|replace)\s+/i", $query)) {
            $this->_affectedRows = @$this->dbh->changes();

            // Take note of the insert id
            if (\preg_match("/^(insert|replace)\s+/i", $query)) {
                $this->insertId = @$this->dbh->lastInsertRowID();
            }

            // Return number of rows affected
            $this->return_val = $this->_affectedRows;

            // Query was an select
        } else {
            // Take note of column info
            $i = 0;
            $this->colInfo = array();
            while ($i < @$this->result->numColumns()) {
                $this->colInfo[$i] = new \stdClass;
                $this->colInfo[$i]->name = $this->result->columnName($i);
                $this->colInfo[$i]->type = null;
                $this->colInfo[$i]->max_length = null;
                $i++;
            }

            // Store Query Results
            $num_rows = 0;
            while ($row = @$this->result->fetchArray(\SQLITE3_ASSOC)) {
                // Store result as an objects within main array
                $obj = (object) $row; //convert to object
                $this->lastResult[$num_rows] = $obj;
                $num_rows++;
            }

            // Log number of rows the query returned
            $this->numRows = $num_rows;

            // Return number of rows selected
            $this->return_val = $this->numRows;
        }
    }

    /**
     * Perform SQLite query and try to determine result value
     * Basic Query    - see docs for more detail
     * @param string
     * @param bool
     * @return bool|mixed
     */
    public function query(string $query, bool $use_prepare = false)
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
        $this->return_val = 0;

        // Flush cached values..
        $this->flush();

        // Log how the function was called
        $this->log_query("\$db->query(\"$query\")");

        // Keep track of the last query for debug..
        $this->lastQuery = $query;

        // If there is no existing database connection then try to connect
        if (!isset($this->dbh) || !$this->dbh) {
            $this->connect($this->database->getPath(), $this->database->getName());
        }

        // Perform the query via std SQLite3 query or SQLite3 prepare function..
        if (!empty($param) && \is_array($param) && ($this->isPrepareOn())) {
            $this->shortcutUsed = true;
            $this->result = $this->query_prepared($query, $param);
        } else {
            $this->result = $this->dbh->query($query);
        }

        if ($this->processQueryResult($query) === false) {
            if ($this->isTransactional)
                throw new \Exception($this->getLastError());

            return false;
        }

        if (!empty($param) && \is_array($param) && $this->isPrepareOn())
            $this->result->finalize();

        // If debug ALL queries
        $this->trace || $this->debugAll ? $this->debug() : null;

        return $this->return_val;
    }

    /**
     * Close the database connection
     */
    public function disconnect()
    {
        if ($this->dbh) {
            $this->dbh = null;
            $this->_connected = false;
        }
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
     * Begin sqlite3 Transaction
     */
    public function beginTransaction()
    {
        $this->dbh->exec('BEGIN;');
        $this->isTransactional = true;
    }

    public function commit()
    {
        $this->dbh->exec('COMMIT;');
        $this->isTransactional = false;
    }

    public function rollback()
    {
        $this->dbh->exec('ROLLBACK;');
        $this->isTransactional = false;
    }
}
