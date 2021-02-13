<?php

declare(strict_types=1);

namespace ezsql\Database;

use Exception;
use ezsql\ezsqlModel;
use ezsql\ConfigInterface;
use ezsql\DatabaseInterface;
use function ezsql\functions\setInstance;

class ez_mysqli extends ezsqlModel implements DatabaseInterface
{
    private $return_val = 0;
    private $is_insert = false;
    private $isTransactional = false;

    /**
     * Database connection handle
     * @var \mysqli
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
    protected $shortcutUsed = false;

    public function __construct(ConfigInterface $settings = null)
    {
        if (empty($settings)) {
            throw new Exception(\MISSING_CONFIGURATION);
        }

        parent::__construct();
        $this->database = $settings;

        if (empty($GLOBALS['ez' . \MYSQLI]))
            $GLOBALS['ez' . \MYSQLI] = $this;
        setInstance($this);
    } // __construct

    public function settings()
    {
        return $this->database;
    }

    /**
     * Short hand way to connect to mysql database server and select a mysql
     * database at the same time
     *
     * @param string $user The database user name
     * @param string $password The database users password
     * @param string $name The name of the database
     * @param string $host The host name or IP address of the database server.
     *                       Default is localhost
     * @param string $charset Encoding of the database
     * @return boolean
     */
    public function quick_connect(
        string $user = '',
        string $password = '',
        string $name = '',
        string $host = '',
        $port = '',
        string $charset = ''
    ) {
        $user = empty($user) ? $this->database->getUser() : $user;
        $password = empty($password) ? $this->database->getPassword() : $password;
        $name = empty($name) ? $this->database->getName() : $name;
        $host = empty($host) ? $this->database->getHost() : $host;
        $port = empty($port) ? $this->database->getPort() : $port;
        $charset = empty($charset) ? $this->database->getCharset() : $charset;

        if (!$this->connect($user, $password, $host, (int) $port, $charset));
        else if (!$this->dbSelect($name, $charset));

        return $this->_connected;
    } // quick_connect

    /**
     * Try to connect to mySQLi database server
     *
     * @param string $user The database user name
     * @param string $password The database users password
     * @param string $host The host name or IP address of the database server.
     *                       Default is localhost
     * @param string $charset The database charset
     *                      Default is empty string
     * @return boolean
     */
    public function connect(
        string $user = '',
        string $password = '',
        string $host = '',
        $port = '',
        string $charset = ''
    ) {
        $this->_connected = false;

        $user = empty($user) ? $this->database->getUser() : $user;
        $password = empty($password) ? $this->database->getPassword() : $password;
        $host = empty($host) ? $this->database->getHost() : $host;
        $port = empty($port) ? $this->database->getPort() : $port;
        $charset = empty($charset) ? $this->database->getCharset() : $charset;

        // Try to establish the server database handle
        if (!$this->dbh = \mysqli_connect($host, $user, $password, $this->database->getName(),  (int) $port)) {
            $this->register_error(\FAILED_CONNECTION . ' in ' . __FILE__ . ' on line ' . __LINE__);
        } else {
            \mysqli_set_charset($this->dbh, $charset);
            $this->_connected = true;
        }

        return $this->_connected;
    } // connect

    /**
     * Try to select the default database for mySQL
     *
     * @param string $name The name of the database
     * @param string $charset Encoding of the database
     * @return boolean
     */
    public function dbSelect($name = '', $charset = '')
    {
        $name = empty($name) ? $this->database->getName() : $name;
        try {
            // Try to connect to the database
            if (($this->dbh === null) || ($this->_connected === false) || !\mysqli_select_db($this->dbh, $name)) {
                throw new Exception("Error Processing Request", 1);
            }

            $this->database->setName($name);
            if ($charset == '') {
                $charset = $this->database->getCharset();
            }

            if ($charset != '') {
                $encoding = \strtolower(\str_replace('-', '', $charset));
                $charsetArray = array();
                $recordSet = \mysqli_query($this->dbh, 'SHOW CHARACTER SET');
                while ($row = \mysqli_fetch_array($recordSet, \MYSQLI_ASSOC)) {
                    $charsetArray[] = $row['Charset'];
                }

                if (\in_array($charset, $charsetArray)) {
                    \mysqli_query($this->dbh, 'SET NAMES \'' . $encoding . '\'');
                }
            }

            return true;
        } catch (\Throwable $e) {
            $str = \FAILED_CONNECTION;
            // Must have an active database connection
            if ($this->dbh && $this->_connected) {
                // Try to get error supplied by mysql if not use our own
                if (!$str = \mysqli_error($this->dbh)) {
                    $str = 'Unexpected error while trying to select database';
                }
            }

            $this->register_error($str . ' in ' . __FILE__ . ' on line ' . __LINE__);
            return false;
        }
    } // select

    /**
     * Format a mySQL string correctly for safe mySQL insert
     * (no matter if magic quotes are on or not)
     *
     * @param string $str
     * @return string
     */
    public function escape(string $str)
    {
        return \mysqli_real_escape_string($this->dbh, \stripslashes($str));
    } // escape

    /**
     * Return mySQLi specific system date syntax
     * i.e. Oracle: SYSDATE Mysql: NOW()
     *
     * @return string
     */
    public function sysDate()
    {
        return 'NOW()';
    }

    /**
     * Helper fetches rows from a prepared result set
     * @param \mysqli_stmt $stmt
     * @param string $query
     * @return bool|\mysqli_result
     */
    private function fetch_prepared_result(&$stmt, $query)
    {
        if ($stmt instanceof \mysqli_stmt) {
            $stmt->store_result();
            $variables = array();
            $is_insert = false;
            $col_info = array();
            if (\preg_match("/^(insert|delete|update|replace)\s+/i", $query)) {
                $this->_affectedRows = \mysqli_stmt_affected_rows($stmt);

                // Take note of the insert id
                if (\preg_match("/^(insert|replace)\s+/i", $query)) {
                    $this->insertId = $stmt->insert_id;
                }
            } else {
                $this->_affectedRows = $stmt->num_rows;
                $meta = $stmt->result_metadata();

                $x = 0;
                // Take note of column info
                while ($field = $meta->fetch_field()) {
                    $col_info[$field->name] = "";
                    $variables[$field->name] = &$col_info[$field->name];
                    $this->colInfo[$x] = $field;
                    $x++;
                }

                // Binds variables to a prepared statement for result storage
                \call_user_func_array([$stmt, 'bind_result'], \array_values($variables));

                $i = 0;
                // Store Query Results
                while ($stmt->fetch()) {
                    // Store results as an objects within main array
                    $resultObject = new \stdClass();
                    foreach ($variables as $key => $value) {
                        $resultObject->$key = $value;
                    }
                    $this->lastResult[$i] = $resultObject;
                    $i++;
                }
            }

            // If there is an error then take note of it..
            if ($str = $stmt->error) {
                $is_insert = true;
                $this->register_error($str);

                // If debug ALL queries
                $this->trace || $this->debugAll ? $this->debug() : null;
                return false;
            }

            // Return number of rows affected
            $return_val = $this->_affectedRows;

            // disk caching of queries
            $this->store_cache($query, $is_insert);

            // If debug ALL queries
            $this->trace || $this->debugAll ? $this->debug() : null;

            return $return_val;
        }

        return false;
    }

    /**
     * Creates a prepared query, binds the given parameters and returns the result of the executed
     * {@link mysqli_stmt}.
     * @param string $query
     * @param array $param
     * @return bool|\mysqli_result
     */
    public function query_prepared(string $query, array $param = null)
    {
        $stmt = $this->dbh->prepare($query);
        if (!$stmt instanceof \mysqli_stmt) {
            if ($this->isTransactional)
                throw new \Exception($this->lastError);

            return false;
        }

        $params = [];
        $types = \array_reduce(
            $param,
            function ($string, &$arg) use (&$params) {
                $params[] = &$arg;
                if (\is_float($arg))
                    $string .= 'd';
                elseif (\is_integer($arg))
                    $string .= 'i';
                elseif (\is_string($arg))
                    $string .= 's';
                else
                    $string .= 'b';

                return  $string;
            },
            ''
        );

        \array_unshift($params, $types);

        \call_user_func_array([$stmt, 'bind_param'], $params);

        $result = ($stmt->execute()) ? $this->fetch_prepared_result($stmt, $query) : false;

        // free and closes a prepared statement
        $stmt->free_result();
        $stmt->close();

        return $result;
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
        if ($str = \mysqli_error($this->dbh)) {
            $this->register_error($str);

            // If debug ALL queries
            $this->trace || $this->debugAll ? $this->debug() : null;
            return false;
        }

        // Query was an insert, delete, update, replace
        $this->is_insert = false;
        if (\preg_match("/^(insert|delete|update|replace)\s+/i", $query)) {
            $this->is_insert = true;
            $this->_affectedRows = \mysqli_affected_rows($this->dbh);

            // Take note of the insert_id
            if (\preg_match("/^(insert|replace)\s+/i", $query)) {
                $this->insertId = \mysqli_insert_id($this->dbh);
            }

            // Return number of rows affected
            $this->return_val = $this->_affectedRows;
        } else {
            // Query was a select
            if (!\is_numeric($this->result) && !\is_bool($this->result)) {

                // Take note of column info
                $i = 0;
                while ($i < \mysqli_num_fields($this->result)) {
                    $this->colInfo[$i] = \mysqli_fetch_field($this->result);
                    $i++;
                }

                // Store Query Results
                $num_rows = 0;
                while ($row = \mysqli_fetch_object($this->result)) {
                    // Store results as an objects within main array
                    $this->lastResult[$num_rows] = $row;
                    $num_rows++;
                }

                \mysqli_free_result($this->result);

                // Log number of rows the query returned
                $this->numRows = $num_rows;

                // Return number of rows selected
                $this->return_val = $this->numRows;
            }
        }
    }

    /**
     * Perform mySQL query and try to determine result value
     *
     * @param string $query
     * @param bool $use_prepare
     * @return bool|mixed
     */
    public function query(string $query, bool $use_prepare = false)
    {
        $param = [];
        if ($use_prepare)
            $param = $this->prepareValues();

        // check for ezQuery placeholder tag and replace tags with proper prepare tag
        $query = \str_replace(\_TAG, '?', $query);

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
        $this->numQueries++;

        // Use core file cache function
        if ($cache = $this->get_cache($query)) {
            return $cache;
        }

        // If there is no existing database connection then try to connect
        if (!isset($this->dbh) || !$this->dbh) {
            $this->connect($this->database->getUser(), $this->database->getPassword(), $this->database->getHost());
            $this->dbSelect($this->database->getName());
        }

        // Perform the query via std mysql_query function..
        if (!empty($param) && \is_array($param) && ($this->isPrepareOn())) {
            $this->shortcutUsed = true;
            return $this->query_prepared($query, $param);
        }

        $this->result = \mysqli_query($this->dbh, $query);

        if ($this->processQueryResult($query) === false) {
            if ($this->isTransactional)
                throw new \Exception($this->lastError);

            return false;
        }

        // disk caching of queries
        $this->store_cache($query, $this->is_insert);

        // If debug ALL queries
        $this->trace || $this->debugAll ? $this->debug() : null;

        return $this->return_val;
    } // query

    /**
     * Close the database connection
     */
    public function disconnect()
    {
        if ($this->dbh) {
            \mysqli_close($this->dbh);
            $this->_connected = false;
        }

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
     * Returns the current database server host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->database->getHost();
    }

    /**
     * Returns the current database server port
     *
     * @return string
     */
    public function getPort()
    {
        return $this->database->getPort();
    }

    /**
     * Returns the current connection charset
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->database->getCharset();
    }

    /**
     * Returns the last inserted Id - auto generated
     *
     * @return int
     */
    public function getInsertId()
    {
        return \mysqli_insert_id($this->dbh);
    } // getInsertId

    /**
     * Begin Mysql Transaction
     */
    public function beginTransaction()
    {
        /* turn autocommit off */
        $this->dbh->autocommit(false);
        $this->dbh->begin_transaction(\MYSQLI_TRANS_START_READ_WRITE);
        $this->isTransactional = true;
    }

    public function commit()
    {
        $this->dbh->commit();
        $this->dbh->autocommit(true);
        $this->isTransactional = false;
    }

    public function rollback()
    {
        $this->dbh->rollBack();
        $this->dbh->autocommit(true);
        $this->isTransactional = false;
    }
} // ez_mysqli
