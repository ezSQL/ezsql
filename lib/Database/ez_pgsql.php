<?php
declare (strict_types = 1);

namespace ezsql\Database;

use Exception;
use ezsql\Configuration;
use ezsql\ezsqlModel;

final class ez_pgsql extends ezsqlModel
{
    /**
     * ezSQL error strings - PostgreSQL
     */
    private $_ezsql_postgresql_str = array
        (
        1 => 'Require $user and $password to connect to a database server',
        2 => 'Error establishing PostgreSQL database connection. Correct user/password? Correct hostname? Database server running?',
        3 => 'Require $dbname to select a database',
        4 => 'mySQL database connection is not active',
        5 => 'Unexpected error while trying to select database',
    );

    private $rows_affected = false;

    protected $preparedValues = array();

    private static $isSecure = false;
    private static $secure = null;

    /**
     * Database configuration setting
     * @var Configuration instance
     */
    private $database;

    public function __construct(Configuration $settings)
    {
        if (empty($settings) || (!$settings instanceof Configuration)) {
            throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
        }
        parent::__construct();
        $this->database = $settings;

        $GLOBALS['db_' . $this->database->getDriver()] = $this;
        \setInstance($this);
    } // __construct

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
        if (!$this->connect($user, $password, $name, $host, $port, true));
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
    public function connect($user = '', $password = '', $name = '', $host = 'localhost', $port = '5432')
    {
        $this->_connected = false;

        $user = empty($user) ? $this->database->getUser() : $user;
        $password = empty($password) ? $this->database->getPassword() : $password;
        $name = empty($name) ? $this->database->getName() : $name;
        $host = ($host != 'localhost') ? $this->database->getHost() : $host;
        $port = ($port != '5432') ? $port : $this->database->getPort();

        $connect_string = "host=" . $host . " port=" . $port . " dbname=" . $name . " user=" . $user . " password=" . $password;

        if (!$user) {
            // Must have a user and a password
            $this->register_error($this->_ezsql_postgresql_str[1] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? \trigger_error($this->_ezsql_postgresql_str[1], \E_USER_WARNING) : null;
        } else if (!$this->dbh = \g_connect($connect_string, true)) {
            // Try to establish the server database handle
            $this->register_error($this->_ezsql_postgresql_str[2] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? \trigger_error($this->_ezsql_postgresql_str[2], \E_USER_WARNING) : null;
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
    public function escape($str)
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
     * Return PostgreSQL specific values: Return all tables of the current schema
     *
     * @return string
     */
    public function showTables()
    {
        return "SELECT table_name FROM information_schema.tables WHERE table_schema = '$this->database->db' AND table_type='BASE TABLE'";
    } // showTables

    /**
     * Return the description of the given table
     *
     * @param string $tbl_name The table name
     * @return string
     */
    public function descTable($tbl_name)
    {
        return "SELECT ordinal_position, column_name, data_type, column_default, is_nullable, character_maximum_length, numeric_precision FROM information_schema.columns WHERE table_name = '$tbl_name' AND table_schema='$this->database->db' ORDER BY ordinal_position";
    } // descTable

    /**
     * Return all databases of the current server
     *
     * @return string
     */
    public function showDatabases()
    {
        return "SELECT datname FROM pg_database WHERE datname NOT IN ('template0', 'template1') ORDER BY 1";
    } // showDatabases

    /**
     * Perform PostgreSQL query and try to determine result value
     *
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
        if (!empty($param) && \is_array($param) && ($this->isPrepareActive()) && (\strpos($query, \_TAG) !== false)) {
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
        $return_val = 0;

        // Flush cached values..
        $this->flush();

        // For reg expressions
        $query = \trim($query);

        // Log how the function was called
        $this->log_query("\$db->query(\"$query\")");

        // Keep track of the last query for debug..
        $this->last_query = $query;

        // Count how many queries there have been
        $this->count(true, true);

        // Use core file cache function
        if ($cache = $this->get_cache($query)) {
            return $cache;
        }

        // If there is no existing database connection then try to connect
        if (!isset($this->dbh) || !$this->dbh) {
            $this->connect($this->database->getUser(),
                $this->database->getPassword(),
                $this->database->getName(),
                $this->database->getHost(),
                $this->database->getPort());
        }

        // Perform the query via std postgresql_query function..
        if (!empty($param) && \is_array($param) && ($this->isPrepareActive())) {
            $this->result = @\pg_query_params($this->dbh, $query, $param);
        } else {
            $this->result = @\pg_query($this->dbh, $query);
        }

        // If there is an error then take note of it..
        if ($str = @\pg_last_error($this->dbh)) {
            $this->register_error($str);
            $this->show_errors ? \trigger_error($str, \E_USER_WARNING) : null;
            return false;
        }
        // Query was an insert, delete, update, replace
        $is_insert = false;
        if (\preg_match("/^(insert|delete|update|replace)\s+/i", $query)) {
            $is_insert = true;
            $this->rows_affected = @\pg_affected_rows($this->result);

            // Take note of the insert_id
            if (\preg_match("/^(insert|replace)\s+/i", $query)) {
                //$this->insert_id = @postgresql_insert_id($this->dbh);
                //$this->insert_id = pg_last_oid($this->result);

                // Thx. Rafael Bernal
                $insert_query = \pg_query("SELECT lastval();");
                $insert_row = \pg_fetch_row($insert_query);
                $this->insert_id = $insert_row[0];
            }

            // Return number for rows affected
            $return_val = $this->rows_affected;

            if (\preg_match("/returning/smi", $query)) {
                while ($row = @\pg_fetch_object($this->result)) {
                    $return_valx[] = $row;
                }
                $return_val = $return_valx;
            }
            // Query was a select
        } else {
            $num_rows = 0;
            //may be needed but my tests did not
            if ($this->result) {
                // Take note of column info
                $i = 0;
                while ($i < @\pg_num_fields($this->result)) {
                    $this->col_info[$i]->name = \pg_field_name($this->result, $i);
                    $this->col_info[$i]->type = \pg_field_type($this->result, $i);
                    $this->col_info[$i]->size = \pg_field_size($this->result, $i);
                    $i++;
                }

                /**
                 * Store Query Results
                 * while ( $row = @pg_fetch_object($this->result, $i, PGSQL_ASSOC) ) doesn't work? donno
                 * while ( $row = @pg_fetch_object($this->result,$num_rows) ) does work
                 */
                while ($row = @\pg_fetch_object($this->result)) {
                    // Store results as an objects within main array
                    $this->last_result[$num_rows] = $row;
                    $num_rows++;
                }

                @\pg_free_result($this->result);
            }
            // Log number of rows the query returned
            $this->num_rows = $num_rows;

            // Return number of rows selected
            $return_val = $this->num_rows;
        }

        // disk caching of queries
        $this->store_cache($query, $is_insert);

        // If debug ALL queries
        $this->trace || $this->debug_all ? $this->debug() : null;

        return $return_val;
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
    } // disconnect

    /**
     * Returns the current database server host
     *
     * @return string
     */
    public function getDBHost()
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
} // ez_pgsql