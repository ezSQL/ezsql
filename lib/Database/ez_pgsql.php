<?php

declare(strict_types=1);

namespace ezsql\Database;

use Exception;
use ezsql\ezsqlModel;
use ezsql\ConfigInterface;
use ezsql\DatabaseInterface;
use function ezsql\functions\setInstance;

class ez_pgsql extends ezsqlModel implements DatabaseInterface
{
    private $return_val = 0;
    private $is_insert = false;
    private $shortcutUsed = false;
    private $isTransactional = false;

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

        if (empty($GLOBALS['ez' . \PGSQL]))
            $GLOBALS['ez' . \PGSQL] = $this;
        setInstance($this);
    } // __construct

    public function settings()
    {
        return $this->database;
    }

    /**
     * In the case of PostgreSQL quick_connect is not really needed because std.
     * connect already does what quick connect does - but for the sake of
     * consistency it has been included
     *
     * @param string $user The database user name
     * @param string $password The database users password
     * @param string $name The name of the database
     * @param string $host The host name or IP address of the database server.
     *            Default is localhost
     * @param string $port The database TCP/IP port
     *          Default is PostgreSQL default port 5432
     * @return boolean
     */
    public function quick_connect($user = '', $password = '', $name = '', $host = 'localhost', $port = '5432')
    {
        if (!$this->connect($user, $password, $name, $host, $port));
        return $this->_connected;
    } // quick_connect

    /**
     * Try to connect to PostgreSQL database server
     *
     * @param string $user The database user name
     * @param string $password The database users password
     * @param string $name The name of the database
     * @param string $host The host name or IP address of the database server.
     *            Default is localhost
     * @param string $port The database TCP/IP port
     *                        Default is PostgreSQL default port 5432
     * @return boolean
     */
    public function connect(
        string $user = '',
        string $password = '',
        string $name = '',
        string $host = 'localhost',
        string $port = '5432'
    ) {
        $this->_connected = false;

        $user = empty($user) ? $this->database->getUser() : $user;
        $password = empty($password) ? $this->database->getPassword() : $password;
        $name = empty($name) ? $this->database->getName() : $name;
        $host = ($host != 'localhost') ? $host : $this->database->getHost();
        $port = ($port != '5432') ? $port : $this->database->getPort();

        $connect_string = "host=" . $host . " port=" . $port . " dbname=" . $name . " user=" . $user . " password=" . $password;

        // Try to establish the server database handle
        if (!$this->dbh = \pg_connect($connect_string, \PGSQL_CONNECT_FORCE_NEW)) {
            $this->register_error(\FAILED_CONNECTION . ' in ' . __FILE__ . ' on line ' . __LINE__);
        } else {
            $this->_connected = true;
        }

        return $this->_connected;
    } // connect

    /**
     * Format a mySQL string correctly for safe mySQL insert
     * (no matter if magic quotes are on or not)
     *
     * @param string $str
     * @return string
     */
    public function escape(string $str)
    {
        return \pg_escape_string(\stripslashes($str));
    } // escape

    /**
     * Return PostgreSQL specific system date syntax
     * i.e. Oracle: SYSDATE Mysql: NOW()
     *
     * @return string
     */
    public function sysDate()
    {
        return 'NOW()';
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
        $result = @\pg_query_params($this->dbh, $query, $param);
        return ($this->shortcutUsed) ? $result : $this->processQueryResult($query, $result);
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

        try {
            // If there is an error then take note of it..
            if ($str = @\pg_last_error($this->dbh)) {
                return $this->register_error($str);
            }
        } catch (\Throwable $ex) {
            return $this->register_error($ex->getMessage());
        }

        // Query was an insert, delete, update, replace
        $this->is_insert = false;
        if (\preg_match("/^(insert|delete|update|replace)\s+/i", $query)) {
            $this->is_insert = true;

            if (\is_bool($this->result))
                return false;

            $this->_affectedRows = @\pg_affected_rows($this->result);

            // Take note of the insert_id
            if (\preg_match("/^(insert|replace)\s+/i", $query)) {
                //$this->insert_id = @postgresql_insert_id($this->dbh);
                //$this->insert_id = pg_last_oid($this->result);

                // Thx. Rafael Bernal
                $insert_query = \pg_query("SELECT lastval();");
                $insert_row = \pg_fetch_row($insert_query);
                $this->insertId = $insert_row[0];
            }

            // Return number for rows affected
            $this->return_val = $this->_affectedRows;

            if (\preg_match("/returning/smi", $query)) {
                while ($row = @\pg_fetch_object($this->result)) {
                    $return_affected[] = $row;
                }
                $this->return_val = $return_affected;
            }
        } else {
            // Query was a select
            $num_rows = 0;
            //may be needed but my tests did not
            if ($this->result) {
                // Take note of column info
                $i = 0;
                while ($i < @\pg_num_fields($this->result)) {
                    $this->colInfo[$i] = new \stdClass();
                    $this->colInfo[$i]->name = \pg_field_name($this->result, $i);
                    $this->colInfo[$i]->type = \pg_field_type($this->result, $i);
                    $this->colInfo[$i]->size = \pg_field_size($this->result, $i);
                    $i++;
                }

                /**
                 * Store Query Results
                 * while ( $row = @pg_fetch_object($this->result, $i, PGSQL_ASSOC) ) doesn't work? donno
                 * while ( $row = @pg_fetch_object($this->result,$num_rows) ) does work
                 */
                while ($row = @\pg_fetch_object($this->result)) {
                    // Store results as an objects within main array
                    $this->lastResult[$num_rows] = $row;
                    $num_rows++;
                }

                @\pg_free_result($this->result);
            }
            // Log number of rows the query returned
            $this->numRows = $num_rows;

            // Return number of rows selected
            $this->return_val = $this->numRows;
        }
    }

    /**
     * Perform PostgreSQL query and try to determine result value
     *
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
        if (!empty($param) && \is_array($param) && ($this->isPrepareOn()) && (\strpos($query, \_TAG) !== false)) {
            foreach ($param as $i => $value) {
                $parameterized = $i + 1;
                $needle = \_TAG;
                $pos = \strpos($query, $needle);
                if ($pos !== false) {
                    $query = \substr_replace($query, '$' . $parameterized, $pos, \strlen($needle));
                }
            }
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
                $this->database->getHost(),
                $this->database->getPort()
            );
        }

        // Perform the query via std postgresql_query function..
        if (!empty($param) && \is_array($param) && ($this->isPrepareOn())) {
            $this->shortcutUsed = true;
            $this->result = $this->query_prepared($query, $param);
        } else {
            try {
                $this->result = @\pg_query($this->dbh, $query);
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
    } // query

    /**
     * Close the database connection
     */
    public function disconnect()
    {
        if ($this->dbh) {
            \pg_close($this->dbh);
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
     * Returns the current database server host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->database->getHost();
    } // getDBHost

    /**
     * Returns the current TCP/IP port
     *
     * @return string
     */
    public function getPort()
    {
        return $this->database->getPort();
    } // getPort

    /**
     * Begin Postgresql Transaction
     */
    public function beginTransaction()
    {
        @\pg_query($this->dbh, "BEGIN");
        $this->isTransactional = true;
    }

    public function commit()
    {
        @\pg_query($this->dbh, "COMMIT");
        $this->isTransactional = false;
    }

    public function rollback()
    {
        @\pg_query($this->dbh, "ROLLBACK");
        $this->isTransactional = false;
    }
} // ez_pgsql
