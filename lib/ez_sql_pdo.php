<?php
/**
 * ezSQL class - PDO
 * Desc..: PDO component (part of ezSQL database abstraction library)
 *
 * @author  Justin Vincent (jv@jvmultimedia.com)
 * @author  Stefanie Janine Stoelting <mail@stefanie-stoelting.de>
 * Contributor:  Lawrence Stubbs <technoexpressnet@gmail.com>
 * @link    http://twitter.com/justinvincent
 * @name    ezSQL_pdo
 * @package ezSQL
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 *
 */
class ezSQL_pdo extends ezSQLcore
{
    /**
     * ezSQL error strings - PDO
     * @var array
     */
    private $_ezsql_pdo_str = array
        (
            1 => 'Require $dsn and $user and $password to create a connection',
            2 => 'File based databases require $dsn to create a connection'
        );

    /**
     * The connection parameter string
     * @var string
     */
    private $_dsn;

    /**
     * The database user name
     * @var string
     */
    private $_dbuser;

    /**
     * The database password
     * @var string
     */
    private $_dbpassword;

    /**
     * The array for connection options, MySQL connection charset, for example
     * @var array
     */
    private $_options;
    
    /**
     * Whether it is a file based database connection, for example to a SQLite
     * database file, or not
     * @var boolean Default is false
     */
    private $_isFileBased=false;

    /**
     * Show errors
     * @var boolean Default is true
     */
    public $show_errors = true;
    
	protected $preparedvalues = array();

    /**
     * Constructor - allow the user to perform a qucik connect at the same time
     * as initialising the ezSQL_sqlite class
     *
     * @param string $dsn The connection parameter string
     *                    Default is empty string
     * @param string $user The database user name
     *                     Default is empty string
     * @param string $password The database password
     *                         Default is empty string
     * @param array $options Array for setting connection options as MySQL
     *                       charset for example
     *                       Default is an empty array
     * @param boolean $isFileBased File based databases like SQLite don't need
     *                             user and password, they work with path in the
     *                             dsn parameter
     *                             Default is false
     */
    public function __construct($dsn='', $user='', $password='', $options=array(), $isFileBased=false) {
        if ( ! class_exists ('PDO') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_pdo requires PDO Lib to be compiled and or linked in to the PHP engine');
        }
        if ( ! class_exists ('ezSQLcore') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_pdo requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');
        }

        parent::__construct();

        // Turn on track errors
        ini_set('track_errors', 1);

        if ( !empty($dsn) && !empty($user) && !empty($password) ) {
            print "<p>constructor: $dsn</p>";
            $this->connect($dsn, $user, $password, $options, $isFileBased);
        }
        
        global $_ezPdo;
        $_ezPdo = $this;
    } // __construct

    /**
     * Try to connect to the database server in the DSN parameters
     *
     * @param string $dsn The connection parameter string
     *                    Default is empty string
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database password
     *                           Default is empty string
     * @param array $options Array for setting connection options as MySQL
     *                       charset for example
     *                       Default is an empty array
     * @param boolean $isFileBased File based databases like SQLite don't need
     *                             user and password, they work with path in the
     *                             dsn parameter
     *                             Default is false
     * @return boolean
     */
    public function connect($dsn='', $dbuser='', $dbpassword='', $options=array(), $isFileBased=false) {
        $this->_connected = false;

        $this->_dsn = empty($dsn) ? $this->_dsn : $dsn;
        $this->_isFileBased = $isFileBased;
        
        if (!$isFileBased) {
            $this->_dbuser = empty($dbuser) ? $this->_dbuser : $dbuser;
            $this->_dbpassword = empty($dbpassword) ? $this->_dbpassword : $dbpassword;
            $this->_options = $options;        

            // Must have a user and a password if not file based
            if ( empty($this->_dsn) || empty($this->_dbuser) || empty($this->_dbpassword) ) {
                $this->register_error($this->_ezsql_pdo_str[1] . ' in ' . __FILE__ . ' on line ' . __LINE__);
                $this->show_errors ? trigger_error($this->_ezsql_pdo_str[1], E_USER_WARNING) : null;
                return false;
            }
        } elseif (empty($this->_dsn)) {
            // Must have a dsn
            $this->register_error($this->_ezsql_pdo_str[2] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->_ezsql_pdo_str[2], E_USER_WARNING) : null;
            return false;
        
        }
        

        // Establish PDO connection
        try  {
            if ($this->_isFileBased) {
                $this->dbh = new PDO($this->_dsn);
                $this->_connected = true;
            } else {
                $this->dbh = new PDO($this->_dsn, $this->_dbuser, $this->_dbpassword, $this->_options);
                $this->_connected = true;
            }
        }
        catch (PDOException $e) {
            $this->register_error($e->getMessage());
            $this->show_errors ? trigger_error($e->getMessage() . '- $dsn: ' . $dsn, E_USER_WARNING) : null;
            return false;
        }

        $this->isConnected = $this->_connected;

        return $this->_connected;
    } // connect

    /**
     * With PDO it is only an alias for the connect method
     *
     * @param string $dsn The connection parameter string
     *                    Default is empty string
     * @param string $user The database user name
     *                     Default is empty string
     * @param string $password The database password
     *                         Default is empty string
     * @param array $options Array for setting connection options as MySQL
     *                       charset for example
     *                       Default is an empty array
     * @param boolean $isFileBased File based databases like SQLite don't need
     *                             user and password, they work with path in the
     *                             dsn parameter
     *                             Default is false
     * @return boolean
     */
    public function quick_connect($dsn='', $user='', $password='', $options=array(), $isFileBased=false) {
        return $this->connect($dsn, $user, $password, $options, $isFileBased);
    } // quick_connect

    /**********************************************************************
    *  Format a SQLite string correctly for safe SQLite insert
    *  (no mater if magic quotes are on or not)
    */

    /**
     * Escape a string with the PDO method
     *
     * @param string $str
     * @return string
     */
    public function escape($str) {
        // If there is no existing database connection then try to connect
        if ( ! isset($this->dbh) || ! $this->dbh ) {
            $this->connect($this->_dsn, $this->user, $this->password, $this->_options, $this->_isFileBased);
        }

        // pdo quote adds ' at the beginning and at the end, remove them for standard behavior
        $return_val = substr($this->dbh->quote($str), 1, -1);

        return $return_val;
    } // escape

    /**
     * Return SQLite specific system date syntax
     * i.e. Oracle: SYSDATE Mysql: NOW()
     *
     * @return string
     */
    public function sysdate() {
        return "datetime('now')";
    } // sysdate

    /**
     * Hooks into PDO error system and reports it to user
     *
     * @return string
     */
    public function catch_error(){
        $error_str = 'No error info';

        $err_array = $this->dbh->errorInfo();

        // Note: Ignoring error - bind or column index out of range
        if ( isset($err_array[1]) && $err_array[1] != 25) {

            $error_str = '';
            foreach ( $err_array as $entry ) {
                $error_str .= $entry . ', ';
            }

            $error_str = substr($error_str, 0, -2);

            $this->register_error($error_str);
            $this->show_errors ? trigger_error($error_str . ' ' . $this->last_query, E_USER_WARNING) : null;

            return true;
        }

    } // catch_error
    
    
    /**
      * Creates a prepared query, binds the given parameters and returns the result of the executed
      * @param string $query
      * @param array $param
      * @param boolean $isselect - return \PDOStatement if select statement otherwise int
      * @return bool \ int \PDOStatement 
      */
    public function query_prepared($query, $param=null, $isselect=false)
    { 
        $stmt = $this->dbh->prepare($query);
        $result = false;
        if( $stmt && $stmt->execute($param) ) {
            $result = $stmt->rowCount();
            while( $stmt->fetch(PDO::FETCH_ASSOC) ) {
            }
        }
        return ($isselect) ? $stmt : $result; 
    }
        
    /**
     * Basic Query	- see docs for more detail
     *
     * @param type $query
     * @return object
     */
    public function query($query, $use_prepare=false) {
        if ($use_prepare)
            $param = &$this->getParamaters();
        
		// check for ezQuery placeholder tag and replace tags with proper prepare tag
		$query = str_replace(_TAG, '?', $query);
            
        // For reg expressions
        $query = str_replace("/[\n\r]/", '', trim($query));

        // Initialize return
        $return_val = 0;

        // Flush cached values..
        $this->flush();

        // Log how the function was called
        $this->log_query("\$db->query(\"$query\")");

        // Keep track of the last query for debug..
        $this->last_query = $query;

        $this->num_queries++;

        // Start timer
        $this->timer_start($this->num_queries);

        // Use core file cache function
        if ( $cache = $this->get_cache($query) ) {
            // Keep tack of how long all queries have taken
            $this->timer_update_global($this->num_queries);

            // Trace all queries
            if ( $this->use_trace_log ) {
                $this->trace_log[] = $this->debug(false);
            }

            return $cache;
        }

        // If there is no existing database connection then try to connect
        if ( ! isset($this->dbh) || ! $this->dbh ) {
            $this->connect($this->_dsn, $this->user, $this->password, $this->_options, $this->_isFileBased);
        }

        // Query was an insert, delete, update, replace
        if ( preg_match("/^(insert|delete|update|replace|drop|create)\s+/i", $query) ) {

            // Perform the query and log number of affected rows
            // Perform the query via std PDO query or PDO prepare function..
            if (!empty($param) && is_array($param) && ($this->getPrepare())) {
                $this->_affectedRows = $this->query_prepared($query, $param, false);	
				$this->setParamaters();
            } else
                $this->_affectedRows = $this->dbh->exec($query);

            // If there is an error then take note of it..
            if ( $this->catch_error() ) {
                return false;
            }

            $is_insert = true;

            // Take note of the insert_id
            if ( preg_match("/^(insert|replace)\s+/i", $query) ) {
                $this->insert_id = @$this->dbh->lastInsertId();
            }

            // Return number of rows affected
            $return_val = $this->_affectedRows;

        } else {
            // Query was an select

            // Perform the query and log number of affected rows
            // Perform the query via std PDO query or PDO prepare function..
            if (!empty($param) && is_array($param) && ($this->getPrepare())) {
                $sth = $this->query_prepared($query, $param, true);	
				$this->setParamaters();
            } else
                $sth = $this->dbh->query($query);

            // If there is an error then take note of it..
            if ( $this->catch_error() ) return false;

            $is_insert = false;

            $col_count = $sth->columnCount();

            for ( $i=0 ; $i < $col_count ; $i++ ) {                
                // Start DEBUG by psc!
                $this->col_info[$i] = new stdClass();
                // End DEBUG by psc
                if ( $meta = $sth->getColumnMeta($i) ) {
                    $this->col_info[$i]->name =  $meta['name'];
                    $this->col_info[$i]->type =  $meta['native_type'];
                    $this->col_info[$i]->max_length =  '';
                } else {
                    $this->col_info[$i]->name =  'undefined';
                    $this->col_info[$i]->type =  'undefined';
                    $this->col_info[$i]->max_length = '';
                }
            }

            // Store Query Results
            $num_rows=0;
            while ( $row = @$sth->fetch(PDO::FETCH_ASSOC) ) {
                // Store results as an objects within main array
                $this->last_result[$num_rows] = (object) $row;
                $num_rows++;
            }

            // Log number of rows the query returned
            $this->num_rows = $num_rows;

            // Return number of rows selected
            $return_val = $this->num_rows;

        }

        // disk caching of queries
        $this->store_cache($query, $is_insert);

        // If debug ALL queries
        $this->trace || $this->debug_all ? $this->debug() : null ;

        // Keep tack of how long all queries have taken
        $this->timer_update_global($this->num_queries);

        // Trace all queries
        if ( $this->use_trace_log ) {
            $this->trace_log[] = $this->debug(false);
        }

        return $return_val;

    } // query

    /**
     * Close the database connection
     */
    public function disconnect(){
        if ($this->dbh) {
            $this->dbh = null;
            $this->_connected = false;
        }
     } // disconnect

} // ezSQL_pdo
