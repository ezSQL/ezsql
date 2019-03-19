<?php
declare(strict_types=1);

namespace ezsql\Database;

use Exception;
use ezsql\ezsqlModel;
use ezsql\ConfigInterface;
use ezsql\DatabaseInterface;

final class ez_pdo extends ezsqlModel implements DatabaseInterface
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
    
    protected $preparedValues = array();

    private static $isSecure = false;
    private static $secure = null;

    /**
    * Database configuration setting 
    * @var Configuration instance
    */
    private static $database;

    public function __construct(ConfigInterface $settings) {
        if ( ! \class_exists ('ezsqlModel') ) {
            if ( ! \interface_exists('Psr\Container\ContainerInterface') )
                throw new Exception(\CONFIGURATION_REQUIRES);
        }
        
        if (empty($settings) || (!$settings instanceof ConfigInterface)) {
            throw new Exception(\MISSING_CONFIGURATION);
        }
        
        parent::__construct();
        $this->database = $settings;

        // Turn on track errors
        ini_set('track_errors', 1);

        if ( !empty($this->database->getDsn()) && !empty($this->database->getUser()) && !empty($this->database->getPassword()) ) {
            $this->connect($this->database->getDsn(), 
                $this->database->getUser(), 
                $this->database->getPassword(), 
                $this->database->getOptions(), 
                $this->database->getIsFile());
        }
        
        $GLOBALS['db_'.\Pdo] = $this;
        \setInstance($this);
    } // __construct

    public function settings()
    {
        return $this->database;
    }

    public static function securePDO(
        $vendor = null, 
        $key = 'certificate.key', 
        $cert = 'certificate.crt', 
        $ca = 'cacert.pem', 
        $path = '.'.\_DS) 
    {
        if (\array_key_exists(\strtolower($vendor), \VENDOR) 
            && (! \file_exists($path.$cert) || ! \file_exists($path.$key)))
            $path = ezQuery::createCertificate();
        elseif ($path == '.'.\_DS) {
            $ssl_path = \getcwd();
            $path = \preg_replace('/\\\/', \_DS, $ssl_path). \_DS;
        }

        if (($vendor == \PGSQL) || ($vendor == \POSTGRESQL)) {
            self::$secure = "sslmode=require;sslcert=".$path.$cert.";sslkey=".$path.$key.";sslrootcert=".$path.$ca.";";
            self::$isSecure = true;
        } elseif (($vendor == \MYSQL) || ($vendor == \MYSQLI)) {
            self::$_options = array(
                \PDO::MYSQL_ATTR_SSL_KEY => $path.$key,
                \PDO::MYSQL_ATTR_SSL_CERT => $path.$cert,
                \PDO::MYSQL_ATTR_SSL_CA => $path.$ca,
                \PDO::MYSQL_ATTR_SSL_CAPATH => $path,
                \PDO::MYSQL_ATTR_SSL_CIPHER => 'DHE-RSA-AES256-SHA',
                \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
            );
        } elseif (($vendor == \SQLSERVER) || ($vendor == \MSSQL) || ($vendor == \SQLSRV)) {
            self::$secure = ";Encrypt=true;TrustServerCertificate=true";
            self::$isSecure = true;
        }
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
    * @param array $options Array for setting connection options as MySQL
    * charset for example
    *                  Default is an empty array
    * @param boolean $isFileBased File based databases like SQLite don't need user and password, 
    * they work with path in the dsn parameter
    *                  Default is false
    * @return boolean
    */
    public function connect($dsn = '', $user = '', $password = '', $options = array(), $isFile = false) 
    {
        $this->_connected = false;

        if (self::$isSecure)
            $setDsn = empty($dsn) ? $this->database->getDsn().$this->secure : $dsn.$this->secure;
        else
            $setDsn = empty($dsn) ? $this->database->getDsn() : $dsn;

        $setUser = empty($user) ? $this->database->getUser() : $user;
        $setPassword = empty($password) ? $this->database->getPassword() : $password; 
        $setOptions = empty($options) ? $this->database->getOptions() : $options;
        
        $IsFile = empty($isFile) ? $this->database->getIsFile() : $isFile;   
        
        if (!$IsFile) {                
            // Must have a user and a password if not file based
            if ( empty($setDsn) || empty($setUser) || empty($setPassword )) {
                $this->register_error($this->_ezsql_pdo_str[1] . ' in ' . __FILE__ . ' on line ' . __LINE__);
                $this->show_errors ? \trigger_error($this->_ezsql_pdo_str[1], \E_USER_WARNING) : null;
            }
        } elseif (empty($setDsn)) {
            // Must have a dsn
            $this->register_error($this->_ezsql_pdo_str[2] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? \trigger_error($this->_ezsql_pdo_str[2], \E_USER_WARNING) : null;        
        }        

        // Establish PDO connection
        try  {
            if ($IsFile) {
                $this->dbh = new \PDO($setDsn, null, null, null);
                $this->_connected = true;
            } else {
                $this->dbh = new \PDO($setDsn, $setUser, $setPassword, $setOptions);
                $this->_connected = true;
            }
        } catch (\PDOException $e) {
            $this->register_error($e->getMessage());
            $this->show_errors ? \trigger_error($e->getMessage() . '- $dsn: ' . $dsn, \E_USER_WARNING) : null;
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
    public function quick_connect($dsn = '', $user = '', $password = '', $options = array(), $isFileBased = false) 
    {
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
        if ( ! isset($this->dbh) || ! $this->dbh ) {
            $this->connect($this->database->getDsn(), 
                $this->database->getUser(), 
                $this->database->getPassword(), 
                $this->database->getOptions(), 
                $this->database->getIsFile());
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
        if ( isset($err_array[1]) && $err_array[1] != 25) {

            $error_str = '';
            foreach ( $err_array as $entry ) {
                $error_str .= $entry . ', ';
            }

            $error_str = \substr($error_str, 0, -2);

            $this->register_error($error_str);
            $this->show_errors ? \trigger_error($error_str . ' ' . $this->last_query, \E_USER_WARNING) : null;

            return true;
        }
    } // catch_error    
    
    /**
    * Creates a prepared query, binds the given parameters and returns the result of the executed
    *
    * @param string $query
    * @param array $param
    * @param boolean $isSelect - return \PDOStatement, if SELECT SQL statement, otherwise int
    * @return bool|int|PDOStatement 
    */
    public function query_prepared(string $query, $param = null, $isSelect = false)
    { 
        $stmt = $this->dbh->prepare($query);
        $result = false;
        if( $stmt && $stmt->execute($param) ) {
            $result = $stmt->rowCount();
            while( $stmt->fetch(\PDO::FETCH_ASSOC) ) {
            }
        }
        return ($isSelect) ? $stmt : $result; 
    }
        
    /**
     * Basic Query	- see docs for more detail
     *
     * @param type $query
     * @return object
     */
    public function query(string $query, $use_prepare = false)
     {
        $param = [];
        if ($use_prepare)
            $param = $this->prepareValues();
        
		// check for ezQuery placeholder tag and replace tags with proper prepare tag
		$query = \str_replace(_TAG, '?', $query);
            
        // For reg expressions
        $query = \str_replace("/[\n\r]/", '', \trim($query));

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
            $this->connect($this->database->getDsn(), 
                $this->database->getUser(), 
                $this->database->getPassword(), 
                $this->database->getOptions(), 
                $this->database->getIsFile());
        }

        // Query was an insert, delete, update, replace
        if ( \preg_match("/^(insert|delete|update|replace|drop|create)\s+/i", $query) ) {

            // Perform the query and log number of affected rows
            // Perform the query via std PDO query or PDO prepare function..
            if (!empty($param) && is_array($param) && ($this->isPrepareActive())) {
                $this->_affectedRows = $this->query_prepared($query, $param, false);
            } else
                $this->_affectedRows = $this->dbh->exec($query);

            // If there is an error then take note of it..
            if ( $this->catch_error() ) {
                return false;
            }

            $is_insert = true;

            // Take note of the insert_id
            if ( \preg_match("/^(insert|replace)\s+/i", $query) ) {
                $this->insert_id = @$this->dbh->lastInsertId();
            }

            // Return number of rows affected
            $return_val = $this->_affectedRows;

        } else {
            // Query was an select

            // Perform the query and log number of affected rows
            // Perform the query via std PDO query or PDO prepare function..
            if (!empty($param) && \is_array($param) && ($this->isPrepareActive())) {
                $sth = $this->query_prepared($query, $param, true);
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
            while ( $row = @$sth->fetch(\PDO::FETCH_ASSOC) ) {
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
    public function disconnect()
    {
        if ($this->dbh) {
            $this->dbh = null;
            $this->_connected = false;
        }
     } // disconnect
} // ez_pdo