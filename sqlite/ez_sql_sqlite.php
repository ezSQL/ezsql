<?php
/**
 * ezSQL class - SQLite 
 * Desc..: SQLite component (part of ezSQL databse abstraction library)
 *
 * @author  Justin Vincent (jv@jvmultimedia.com)
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @link    http://twitter.com/justinvincent
 * @name    ezSQL_sqlite
 * @package ezSQL
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 *
 */
class ezSQL_sqlite extends ezSQLcore
{
    /**
     * ezSQL error strings - SQLite
     * @var array
     */
    private $ezsql_sqlite_str = array
        (
            1 => 'Require $dbpath and $dbname to open an SQLite database'
        );
    
    /**
     * Show errors
     * @var boolean Default is true
     */
    public $show_errors = true;

    /**
     * Constructor - allow the user to perform a qucik connect at the same time 
     * as initialising the ezSQL_sqlite class
     *
     * @param string $dbpath Path to the SQLite file
     *                       Default is empty string
     * @param string $dbname Name of the database
     *                       Default is empty string
     */
    public function __construct($dbpath='', $dbname='') {
        if ( ! function_exists ('sqlite_open') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_sqlite requires SQLite Lib to be compiled and or linked in to the PHP engine');
        }
        if ( ! class_exists ('ezSQLcore') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_sqlite requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');
        }

        parent::__construct();

        // Turn on track errors 
        ini_set('track_errors',1);

        if ( $dbpath && $dbname ) {
            $this->connect($dbpath, $dbname);
        }
    } // __construct

    /**
     * Try to connect to SQLite database server
     *
     * @param string $dbpath Path to the SQLite file
     *                       Default is empty string
     * @param string $dbname Name of the database
     *                       Default is empty string
     * @return boolean 
     */
    public function connect($dbpath='', $dbname='') {
        $return_val = false;

        // Must have a user and a password
        if ( ! $dbpath || ! $dbname ) {
            $this->register_error($this->ezsql_sqlite_str[1] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_sqlite_str[1], E_USER_WARNING) : null;
        }  else if ( ! $this->dbh = @sqlite_open($dbpath . $dbname) ) {
            // Try to establish the server database handle
            $this->register_error($php_errormsg);
            $this->show_errors ? trigger_error($php_errormsg, E_USER_WARNING) : null;
        }
        else
            $return_val = true;

        return $return_val;			
    } // connect

    /**
     * In the case of SQLite quick_connect is not really needed because std. 
     * connect already does what quick connect does - but for the sake of 
     * consistency it has been included
     *
     * @param string $dbpath Path to the SQLite file
     *                       Default is empty string
     * @param string $dbname Name of the database
     *                       Default is empty string
     * @return boolean
     */
    public function quick_connect($dbpath='', $dbname='') {
        return $this->connect($dbpath, $dbname);
    } // quick_connect

    /**
     * No real equivalent of mySQL select in SQLite once again, function 
     * included for the sake of consistency
     *
     * @param string $dbpath Path to the SQLite file
     *                       Default is empty string
     * @param string $dbname Name of the database
     *                       Default is empty string
     * @return boolean
     */
    public function select($dbpath='', $dbname='') {
        return $this->connect($dbpath, $dbname);
    } // select

    /**
     * Format a SQLite string correctly for safe SQLite insert
     * (no matter if magic quotes are on or not)
     *
     * @param string $str
     * @return string
     */
    public function escape($str) {
        return sqlite_escape_string(stripslashes(preg_replace("/[\r\n]/",'',$str)));				
    } // escape

    /**
     * Return SQLite specific system date syntax 
     * i.e. Oracle: SYSDATE Mysql: NOW()
     *
     * @return string 
     */
    public function sysdate() {
        return 'now';			
    } // sysdate

    /**
     * Perform SQLite query and try to detirmin result value
     * Basic Query	- see docs for more detail
     *
     * @param string $query
     * @return object
     */
    public function query($query) {

        // For reg expressions
        $query = str_replace("/[\n\r]/", '', trim($query)); 

        // Initialise return
        $return_val = 0;

        // Flush cached values..
        $this->flush();

        // Log how the function was called
        $this->func_call = "\$db->query(\"$query\")";

        // Keep track of the last query for debug..
        $this->last_query = $query;

        // Perform the query via std mysql_query function..
        $this->result = @sqlite_query($this->dbh, $query);
        $this->num_queries++;

        // If there is an error then take note of it..
        if (@sqlite_last_error($this->dbh)){
            $err_str = sqlite_error_string (sqlite_last_error($this->dbh));
            $this->register_error($err_str);
            $this->show_errors ? trigger_error($err_str, E_USER_WARNING) : null;
            return false;
        }

        // Query was an insert, delete, update, replace
        if ( preg_match("/^(insert|delete|update|replace)\s+/i", $query) ) {
            $this->rows_affected = @sqlite_changes($this->dbh);

            // Take note of the insert_id
            if ( preg_match("/^(insert|replace)\s+/i", $query) ) {
                $this->insert_id = @sqlite_last_insert_rowid($this->dbh);	
            }

            // Return number fo rows affected
            $return_val = $this->rows_affected;

        }  else {
            // Query was an select 

            // Take note of column info	
            $i=0;
            while ($i < @sqlite_num_fields($this->result)) {
                $this->col_info[$i]->name       = sqlite_field_name ($this->result, $i);
                $this->col_info[$i]->type       = null;
                $this->col_info[$i]->max_length = null;
                $i++;
            }

            // Store Query Results
            $num_rows=0;
            while ($row =  @sqlite_fetch_array($this->result, SQLITE_ASSOC)) {
                // Store relults as an objects within main array
                $obj= (object) $row; //convert to object
                $this->last_result[$num_rows] = $obj;
                $num_rows++;
            }

            // Log number of rows the query returned
            $this->num_rows = $num_rows;

            // Return number of rows selected
            $return_val = $this->num_rows;
        }

        // If debug ALL queries
        $this->trace||$this->debug_all ? $this->debug() : null ;

        return $return_val;

    } // query

    /**
     * Close the database connection
     */
    public function disconnect(){
         $this->dbh = null;
     } // disconnect

} // ezSQL_sqlite