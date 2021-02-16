<?php

declare(strict_types=1);

namespace ezsql\Database;

use Exception;
use ezsql\ezsqlModel;
use ezsql\ConfigInterface;
use ezsql\DatabaseInterface;
use function ezsql\functions\setInstance;

class ez_pdo extends ezsqlModel implements DatabaseInterface
{
    private $return_val = 0;
    private $is_insert = false;
    private $shortcutUsed = false;
    private $isTransactional = false;

    /**
     * Database connection handle
     * @var \PDO
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

        // Turn on track errors
        ini_set('track_errors', '1');

        if (empty($GLOBALS['ez' . \Pdo]))
            $GLOBALS['ez' . \Pdo] = $this;
        setInstance($this);
    } // __construct

    public function settings()
    {
        return $this->database;
    }

    /**
     * Try to connect to the database server in the DSN parameters
     *
     * @param string $dsn The connection parameter string
     *                  Default is empty string
     * @param string $user The database user name
     *                  Default is empty string
     * @param string $password The database password
     *                  Default is empty string
     * @param array $options Array for setting connection options
     *                  Default is an empty array
     * @param boolean $isFileBased File based databases like SQLite don't need user and password,
     *                  Default is false
     * @return boolean
     */
    public function connect(
        $dsn = '',
        $user = '',
        $password = '',
        $options = array(),
        $isFile = false
    ) {
        $this->_connected = false;
        $key = $this->sslKey;
        $cert = $this->sslCert;
        $ca = $this->sslCa;
        $path = $this->sslPath;

        $vendor = $this->database->getDsn();
        if ($this->isSecure) {
            if (\strpos($vendor, \PGSQL) !== false) {
                $this->secureOptions = 'sslmode=require;sslcert=' . $path . $cert . ';sslkey=' . $path . $key . ';sslrootcert=' . $path . $ca . ';';
            } elseif (\strpos($vendor, 'mysql') !== false) {
                $this->secureOptions = array(
                    \PDO::MYSQL_ATTR_SSL_KEY => $path . $key,
                    \PDO::MYSQL_ATTR_SSL_CERT => $path . $cert,
                    \PDO::MYSQL_ATTR_SSL_CA => $path . $ca,
                    \PDO::MYSQL_ATTR_SSL_CAPATH => $path,
                    \PDO::MYSQL_ATTR_SSL_CIPHER => 'DHE-RSA-AES256-SHA',
                    \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
                );
            } elseif (\strpos($vendor, \MSSQL) !== false) {
                $this->secureOptions = ';Encrypt=true;TrustServerCertificate=true';
            }
        }

        if ($this->isSecure && \is_string($this->secureOptions))
            $dsn = empty($dsn) ? $vendor . $this->secureOptions : $dsn . $this->secureOptions;
        else
            $dsn = empty($dsn) ? $vendor : $dsn;

        if ($this->isSecure && \is_array($this->secureOptions))
            $options = $this->secureOptions;
        else
            $options = empty($options) ? $this->database->getOptions() : $options;

        $user = empty($user) ? $this->database->getUser() : $user;
        $password = empty($password) ? $this->database->getPassword() : $password;
        $isFile = empty($isFile) ? $this->database->getIsFile() : $isFile;

        // Establish PDO connection
        try {
            if ($isFile) {
                $this->dbh = new \PDO($dsn, null, null, null);
                $this->_connected = true;
            } else {
                $this->dbh = new \PDO($dsn, $user, $password, $options);
                $this->_connected = true;
            }
        } catch (\PDOException $e) {
            $this->register_error($e->getMessage() . '- $dsn: ' . $dsn);
        }

        return $this->_connected;
    } // connect

    /**
     * With PDO it is only an alias for the connect method
     *
     * @param string $dsn The connection parameter string
     *   - Default is empty string
     * @param string $user The database user name
     *   - Default is empty string
     * @param string $password The database password
     *   - Default is empty string
     * @param array $options Array for setting connection options
     *   - Default is an empty array
     * @param boolean $isFileBased File based databases
     * like SQLite don't need user and password, they work with path in the dsn parameter
     *   - Default is false
     * @return boolean
     */
    public function quick_connect(
        $dsn = '',
        $user = '',
        $password = '',
        $options = array(),
        $isFileBased = false
    ) {
        return $this->connect($dsn, $user, $password, $options, $isFileBased);
    } // quick_connect

    /**
     *  Format a SQLite string correctly for safe SQLite insert
     *  (no mater if magic quotes are on or not)
     */

    /**
     * Escape a string with the PDO method
     *
     * @param string $str
     * @return string
     */
    public function escape(string $str)
    {
        // If there is no existing database connection then try to connect
        if (!isset($this->dbh) || !$this->dbh) {
            $this->connect(
                $this->database->getDsn(),
                $this->database->getUser(),
                $this->database->getPassword(),
                $this->database->getOptions(),
                $this->database->getIsFile()
            );
        }

        // pdo quote adds ' at the beginning and at the end, remove them for standard behavior
        $return_val = \substr($this->dbh->quote($str), 1, -1);

        return $return_val;
    } // escape

    /**
     * Return SQLite specific system date syntax
     * i.e. Oracle: SYSDATE Mysql: NOW()
     *
     * @return string
     */
    public function sysDate()
    {
        return "datetime('now')";
    }

    /**
     * Hooks into PDO error system and reports it to user
     *
     * @return string
     */
    public function catch_error()
    {
        $error_str = 'No error info';

        $err_array = $this->dbh->errorInfo();

        // Note: Ignoring error - bind or column index out of range
        if (isset($err_array[1]) && $err_array[1] != 25) {

            $error_str = '';
            foreach ($err_array as $entry) {
                $error_str .= $entry . ', ';
            }

            $error_str = \substr($error_str, 0, -2);

            $this->register_error($error_str . ' ' . $this->lastQuery);

            return true;
        }
    } // catch_error

    /**
     * Creates a prepared query, binds the given parameters and returns the result of the executed
     *
     * @param string $query
     * @param array $param
     * @param boolean $isSelect - return \PDOStatement, if SELECT SQL statement, otherwise int
     * @return bool|int|\PDOStatement
     */
    public function query_prepared(string $query, array $param = null, $isSelect = false)
    {
        $stmt = $this->dbh->prepare($query);
        $result = false;
        if ($stmt && $stmt->execute(\array_values($param))) {
            $result = $stmt->rowCount();
            // Store Query Results
            $num_rows = 0;
            try {
                while ($row = @$stmt->fetch(\PDO::FETCH_ASSOC)) {
                    // Store results as an objects within main array
                    $this->lastResult[$num_rows] = (object) $row;
                    $num_rows++;
                }
            } catch (\Throwable $ex) {
                //
            }

            $this->numRows = $num_rows;
        }

        $return = ($isSelect) ? $stmt : $result;
        if ($this->shortcutUsed)
            return $return;

        $status = ((\strpos($query, 'SELECT ') !== false) || (\strpos($query, 'select ') !== false));
        $prepareOnly = ($status) ? $stmt : $result;
        return $this->processResult($query, $prepareOnly, $status);
    }

    /**
     * Perform post processing on SQL query call
     *
     * @param string $query
     * @param mixed $result
     * @param bool $isSelect
     * @return bool|void
     */
    private function processResult(string $query, $result = null, bool $isSelect = false)
    {
        $this->shortcutUsed = false;

        // If there is an error then take note of it..
        if ($this->catch_error()) {
            return false;
        }

        if ($isSelect) {
            $this->is_insert = false;

            if (!empty($result)) {
                $col_count = $result->columnCount();
                for ($i = 0; $i < $col_count; $i++) {
                    // Start DEBUG by psc!
                    $this->colInfo[$i] = new \stdClass();
                    // End DEBUG by psc
                    if ($meta = $result->getColumnMeta($i)) {
                        $this->colInfo[$i]->name =  $meta['name'];
                        $this->colInfo[$i]->type =  $meta['native_type'];
                        $this->colInfo[$i]->max_length =  '';
                    } else {
                        $this->colInfo[$i]->name =  'undefined';
                        $this->colInfo[$i]->type =  'undefined';
                        $this->colInfo[$i]->max_length = '';
                    }
                }

                // Store Query Results
                $num_rows = 0;
                try {
                    while ($row = @$result->fetch(\PDO::FETCH_ASSOC)) {
                        // Store results as an objects within main array
                        $this->lastResult[$num_rows] = (object) $row;
                        $num_rows++;
                    }
                } catch (\Throwable $ex) {
                    //
                }

                // Log number of rows the query returned
                $this->numRows = empty($num_rows) ? $this->numRows : $num_rows;

                // Return number of rows selected
                $this->return_val = $this->numRows;
            }
        } else {
            $this->is_insert = true;

            if (!empty($result))
                $this->_affectedRows = $result;

            try {
                // Take note of the insert_id
                if (\preg_match("/^(insert|replace)\s+/i", $query)) {
                    $this->insertId = @$this->dbh->lastInsertId();
                }
            } catch (\Throwable $ex) {
                //
            }

            // Return number of rows affected
            $this->return_val = $this->_affectedRows;
        }

        return $this->return_val;
    }

    /**
     * Perform SQL query
     *
     * @param string $query
     * @param array $param
     * @return bool|void
     */
    private function processQuery(string $query, array $param = null)
    {
        // Query was an insert, delete, update, replace
        if (\preg_match("/^(insert|delete|update|replace|drop|create)\s+/i", $query)) {
            // Perform the query and log number of affected rows
            // Perform the query via std PDO query or PDO prepare function..
            if (!empty($param) && \is_array($param) && $this->isPrepareOn()) {
                $this->shortcutUsed = true;
                $this->_affectedRows = $this->query_prepared($query, $param, false);
            } else {
                try {
                    $this->_affectedRows = $this->dbh->exec($query);
                } catch (\Throwable $ex) {
                    //
                }
            }

            if ($this->processResult($query) === false)
                return false;
        } else {
            // Query was an select

            // Perform the query and log number of affected rows
            // Perform the query via std PDO query or PDO prepare function..
            if (!empty($param) && \is_array($param) && $this->isPrepareOn()) {
                $this->shortcutUsed = true;
                $sth = $this->query_prepared($query, $param, true);
            } else
                try {
                    $sth = $this->dbh->query($query);
                } catch (\Throwable $ex) {
                    //
                }

            if ($this->processResult($query, $sth, true) === false)
                return false;
        }
    }

    /**
     * Basic Query	- see docs for more detail
     *
     * @param string $query
     * @param bool $use_prepare
     * @return object
     */
    public function query(string $query, bool $use_prepare = false)
    {
        $param = [];
        if ($use_prepare)
            $param = $this->prepareValues();

        // check for ezQuery placeholder tag and replace tags with proper prepare tag
        $query = \str_replace(_TAG, '?', $query);

        // For reg expressions
        $query = \str_replace("/[\n\r]/", '', \trim($query));

        // Initialize return
        $this->return_val = 0;

        // Flush cached values..
        $this->flush();

        // Log how the function was called
        $this->log_query("\$db->query(\"$query\")");

        // Keep track of the last query for debug..
        $this->lastQuery = $query;

        $this->numQueries++;

        // Start timer
        $this->timer_start($this->numQueries);

        // Use core file cache function
        if ($cache = $this->get_cache($query)) {
            // Keep tack of how long all queries have taken
            $this->timer_update_global($this->numQueries);

            // Trace all queries
            if ($this->useTraceLog) {
                $this->traceLog[] = $this->debug(false);
            }

            return $cache;
        }

        // If there is no existing database connection then try to connect
        if (!isset($this->dbh) || !$this->dbh) {
            $this->connect(
                $this->database->getDsn(),
                $this->database->getUser(),
                $this->database->getPassword(),
                $this->database->getOptions(),
                $this->database->getIsFile()
            );
        }

        if ($this->processQuery($query, $param) === false) {
            if ($this->isTransactional)
                throw new \PDOException($this->getLastError());

            return false;
        }

        // disk caching of queries
        $this->store_cache($query, $this->is_insert);

        // If debug ALL queries
        $this->trace || $this->debugAll ? $this->debug() : null;

        // Keep tack of how long all queries have taken
        $this->timer_update_global($this->numQueries);

        // Trace all queries
        if ($this->useTraceLog) {
            $this->traceLog[] = $this->debug(false);
        }

        return $this->return_val;
    } // query

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
     * Begin PDO Transaction
     */
    public function beginTransaction()
    {
        $this->dbh->beginTransaction();
        $this->isTransactional = true;
    }

    public function commit()
    {
        $this->dbh->commit();
        $this->isTransactional = false;
    }

    public function rollback()
    {
        $this->dbh->rollback();
        $this->isTransactional = false;
    }
} // ez_pdo
