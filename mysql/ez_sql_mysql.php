<?php
/**
 * ezSQL Database specific class - mySQL
 * Desc..: mySQL component (part of ezSQL databse abstraction library)
 * 
 * @author  Justin Vincent (jv@jvmultimedia.com)
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @link    http://twitter.com/justinvincent
 * @name    ezSQL_mysql
 * @package ezSQL
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 * 
 */
class ezSQL_mysql extends ezSQLcore
{
    /*
     * ezSQL error strings - mySQL
     * @var array
     */
    private $ezsql_mysql_str = array
        (
            1 => 'Require $dbuser and $dbpassword to connect to a database server',
            2 => 'Error establishing mySQL database connection. Correct user/password? Correct hostname? Database server running?',
            3 => 'Require $dbname to select a database',
            4 => 'mySQL database connection is not active',
            5 => 'Unexpected error while trying to select database'
        );

    
    /**
     * Database user name
     * @var string
     */
    private $dbuser;

    /**
     * Database password for the given user
     * @var string
     */
    private $dbpassword;

    /**
     * Database name
     * @var string
     */
    private $dbname;

    /**
     * Host name or IP address
     * @var string
     */
    private $dbhost;
    
    /**
     * Database charset
     * @var string Default is utf8
     */
    private $charset = 'utf8';
    
    /**
     * Show errors
     * @var boolean Default is true
     */
    public $show_errors = true;

    /**
     * Constructor - allow the user to perform a qucik connect at the same time 
     * as initialising the ezSQL_mysql class
     *
     * @param string $dbuser The database user name
     * @param string $dbpassword The database users password
     * @param string $dbname The name of the database
     * @param string $dbhost The host name or IP address of the database server.
     *                       Default is localhost
     * @param string $charset The database charset
     *                        Default is empty string
     */
    public function __construct($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $charset='') {
        if ( ! function_exists ('mysql_connect') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_mysql requires mySQL Lib to be compiled and or linked in to the PHP engine');
        }
        if ( ! class_exists ('ezSQLcore') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_mysql requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');
        }
       
        parent::__construct();
       
        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbname = $dbname;
        $this->dbhost = $dbhost;
        if ( ! empty($charset) ) {
            $this->charset = $charset;
        }
    } // __construct

    /**
     * Short hand way to connect to mssql database server and select a mssql
     * database at the same time
     *
     * @param string $dbuser The database user name
     * @param string $dbpassword The database users password
     * @param string $dbname The name of the database
     * @param string $dbhost The host name or IP address of the database server.
     *                       Default is localhost
     * @return boolean 
     */
    public function quick_connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost') {
        if ( ! $this->connect($dbuser, $dbpassword, $dbhost, true) ) ;
        else if ( ! $this->select($dbname) ) ;
        
        return $this->connected;
    } // quick_connect

    /**
     * Try to connect to mySQL database server
     *
     * @param string $dbuser The database user name
     * @param string $dbpassword The database users password
     * @param string $dbhost The host name or IP address of the database server.
     *                       Default is localhost
     * @param type $charset The database charset
     *                      Default is empty string
     * @return boolean 
     */
    public function connect($dbuser='', $dbpassword='', $dbhost='localhost', $charset='') {
        $this->connected = false;
        
        $this->dbuser = empty($dbuser) ? $this->dbuser : $dbuser;
        $this->dbpassword = empty($dbpassword) ? $this->dbpassword : $dbpassword;
        $this->dbhost = $dbhost!='localhost' ? $this->dbhost : $dbhost;
        $this->charset = empty($charset) ? $this->charset : $charset;
       
        // Must have a user and a password
        if ( empty($this->dbuser) ) {
            $this->register_error($this->ezsql_mysql_str[1] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_mysql_str[1], E_USER_WARNING) : null;
        } else if ( ! $this->dbh = @mysql_connect($this->dbhost, $this->dbuser, $this->dbpassword, true, 131074) ) {
            // Try to establish the server database handle
            $this->register_error($this->ezsql_mysql_str[2] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_mysql_str[2], E_USER_WARNING) : null;
        } else {
            mysql_set_charset($this->charset, $this->dbh);
            $this->connected = true;
        }

        return $this->connected;
    } // connect

    /**
     * Try to select a mySQL database
     *
     * @param string $dbname The name of the database
     * @return boolean 
     */
    public function select($dbname='') {
        if ( ! $dbname ) {
            // Must have a database name
            $this->register_error($this->ezsql_mysql_str[3] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_mysql_str[3], E_USER_WARNING) : null;
        } else if ( ! $this->dbh ) {
            // Must have an active database connection
            $this->register_error($this->ezsql_mysql_str[4] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_mysql_str[4], E_USER_WARNING) : null;
        } else if ( !@mysql_select_db($dbname, $this->dbh) ) {
            // Try to connect to the database
            // Try to get error supplied by mysql if not use our own
            if ( !$str = @mysql_error($this->dbh)) {
                $str = $this->ezsql_mysql_str[5];
            }

            $this->register_error($str . ' in ' .__FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($str, E_USER_WARNING) : null;
        } else {
            $this->dbname = $dbname;
            $this->connected = true;
        }

        return $this->connected;
    } // select

    /**
     * Format a mySQL string correctly for safe mySQL insert
     * (no matter if magic quotes are on or not)
     *
     * @param string $str
     * @return string 
     */
    public function escape($str) {
        return mysql_real_escape_string(stripslashes($str));
    } // escape

    /**
     * Return mySQL specific system date syntax
     * i.e. Oracle: SYSDATE Mysql: NOW()
     * 
     * @return string 
     */
    public function sysdate() {
        return 'NOW()';
    } // sysdate

    /**
     * Perform mySQL query and try to determine result value
     *
     * @param type $query
     * @return boolean 
     */
    public function query($query) {

        // Initialise return
        $return_val = 0;

        // Flush cached values..
        $this->flush();

        // For reg expressions
        $query = trim($query);

        // Log how the function was called
        $this->func_call = "\$db->query(\"$query\")";

        // Keep track of the last query for debug..
        $this->last_query = $query;

        // Count how many queries there have been
        $this->num_queries++;

        // Use core file cache function
        if ( $cache = $this->get_cache($query) ) {
            return $cache;
        }

        // If there is no existing database connection then try to connect
        if ( ! isset($this->dbh) || ! $this->dbh ) {
            $this->connect($this->dbuser, $this->dbpassword, $this->dbhost);
            $this->select($this->dbname);
        }

        // Perform the query via std mysql_query function..
        $this->result = @mysql_query($query,$this->dbh);

        // If there is an error then take note of it..
        if ( $str = @mysql_error($this->dbh) ) {
            $is_insert = true;
            $this->register_error($str);
            $this->show_errors ? trigger_error($str,E_USER_WARNING) : null;
            return false;
        }

        // Query was an insert, delete, update, replace
        $is_insert = false;
        if ( preg_match("/^(insert|delete|update|replace)\s+/i", $query) ) {
            $this->affectedRows = @mysql_affected_rows($this->dbh);

            // Take note of the insert_id
            if ( preg_match("/^(insert|replace)\s+/i", $query) ) {
                $this->insert_id = @mysql_insert_id($this->dbh);
            }

            // Return number fo rows affected
            $return_val = $this->affectedRows;
        } else {
            // Query was a select

            // Take note of column info
            $i=0;
            while ($i < @mysql_num_fields($this->result)) {
                $this->col_info[$i] = @mysql_fetch_field($this->result);
                $i++;
            }

            // Store Query Results
            $num_rows=0;
            while ( $row = @mysql_fetch_object($this->result) ) {
                // Store relults as an objects within main array
                $this->last_result[$num_rows] = $row;
                $num_rows++;
            }

            @mysql_free_result($this->result);

            // Log number of rows the query returned
            $this->num_rows = $num_rows;

            // Return number of rows selected
            $return_val = $this->num_rows;
        }

        // disk caching of queries
        $this->store_cache($query, $is_insert);

        // If debug ALL queries
        $this->trace || $this->debug_all ? $this->debug() : null ;

        return $return_val;
    } // query

    /**
     * Close the database connection
     */    
    public function disconnect() {
        if ( $this->dbh ) {
            mysql_close($this->dbh);
            $this->connected = false;
        }
        
        $this->connected = false;
    } // function
    
    /**
     * Returns the current database server host
     *
     * @return string
     */
    public function getDBHost() {
        return $this->dbhost;
    } // getDBHost

    /**
     * Returns the current connection charset
     *
     * @return string
     */
    public function getCharset() {
        return $this->charset;
    } // getCharset

    /**
     * Returns the last inserted autoincrement
     * 
     * @return int
     */
    public function getInsertId() {
        return mysql_insert_id();
    } // getInsertId
} // ezSQL_mysql