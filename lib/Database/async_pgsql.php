<?php
declare(strict_types=1);

namespace ezsql\Database;

use Exception;
use ezsql\ConfigInterface;
use ezsql\Database\ez_pgsql;
use ezsql\Database\async_interface;

class async_pgsql extends ez_pgsql implements async_interface
{
    public function __construct(ConfigInterface $settings = null)
    {        
        if (empty($settings)) {
            throw new Exception(\MISSING_CONFIGURATION);
        }
        
        parent::__construct();
        $this->database = $settings;

        if (empty($GLOBALS['async'.\PGSQL]))
            $GLOBALS['async'.\PGSQL] = $this;
        \setInstance($this);

        // Prepare statement usage not possible with async queries.
        $this->prepareOff();
    } // __construct

    /**
     * Creates a prepared query, binds the given parameters and returns the result of the executed
     *
     * @param string $query
     * @param array $param
     * @return bool|mixed
     */
    public function query_prepared(string $query, array $param = null)
    {
        return false;
    }
    
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
        string $port = '5432')
    {
        $this->_connected = false;

        $user = empty($user) ? $this->database->getUser() : $user;
        $password = empty($password) ? $this->database->getPassword() : $password;
        $name = empty($name) ? $this->database->getName() : $name;
        $host = ($host != 'localhost') ? $host : $this->database->getHost();
        $port = ($port != '5432') ? $port : $this->database->getPort();

        $connect_string = "host=".$host." port=".$port." dbname=".$name." user=".$user." password=".$password;

        // Try to establish the server database handle
        if (!$this->dbh = \pg_connect($connect_string, \PGSQL_CONNECT_ASYNC | \PGSQL_CONNECT_FORCE_NEW)) {
            $this->register_error(\FAILED_CONNECTION . ' in ' . __FILE__ . ' on line ' . __LINE__);
        } else {
            $this->_connected = true;
        }

        return $this->_connected;
    } // connect

    /**
     * The documentation on this function is pretty barebones (as is the case for a lot of thin PHP wrappers around C functions), 
     * but from what I've gathered by reading the libpq doc and trying various things, you should probably know the following :

     * Polling the connection while the underlying socket is busy will cause the connection (or at least the polling, 
     * I'm not sure) to fail.
     * 
     * As stated by the libpq documentation, "do not assume that the socket remains the same across PQconnectPoll calls"
     * The socket will become ready after every change in connection status, 
     * so the connection must be polled multiple times until the function returns "polling_ok" or "polling_failed".
     * "polling_active" can never be returned by libpq and has literally never been used anywhere ever, it has been an unused constant since day 1.
     * What you need to do is use pg_socket get a PHP stream object corresponding to the current socket and wait after it before polling, like so:
     */
    public function query_wait() {
        $conn = $this->dbh;
        assert(\is_resource($conn));
        assert(\get_resource_type($conn) === "pgsql link" || \get_resource_type($conn) === "pgsql link persistent");

        // "On the first iteration, i.e. if you have yet to call PQconnectPoll, behave as if it last returned PGRES_POLLING_WRITING."
        $poll_outcome = \PGSQL_POLLING_WRITING;

        while (true) {
            $socket = [\pg_socket($conn)]; // "Caution: do not assume that the socket remains the same across `pg_connect_poll` calls."
            \stream_set_blocking($socket, false);
            
            if (!$socket) {
                $this->register_error(\FAILED_CONNECTION . ' in ' . __FILE__ . ' on line ' . __LINE__);
            }

            $null = [];

            if ($poll_outcome === \PGSQL_POLLING_READING) {
                \stream_select($socket, $null, $null, 5);
                $poll_outcome = \pg_connect_poll($conn);
            } elseif ($poll_outcome === \PGSQL_POLLING_WRITING) {
                \stream_select($null, $socket, $null, 5);
                $poll_outcome = \pg_connect_poll($conn);
            } elseif ($poll_outcome === \PGSQL_POLLING_FAILED) {
                $this->register_error(\FAILED_CONNECTION . ' in ' . __FILE__ . ' on line ' . __LINE__);
            } else {
                break;
            }
            yield;
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
        // Initialize return
        $this->return_val = 0;

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

        // Do things while the connection is getting ready
        yield $this->query_wait();
        @\pg_send_query($this->dbh, $query);
        $result = \pg_get_result($this->dbh);

        if ($this->processQueryResult($query, $result) === false)
            return false;

        // disk caching of queries
        $this->store_cache($query, $this->is_insert);

        // If debug ALL queries
        $this->trace || $this->debug_all ? $this->debug() : null;

        return $this->return_val;
    } // query
    
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