<?php
/**
 * ezSQL class - Sybase ASE
 * Desc..: Sybase ASE component (part of ezSQL database abstraction library) -
 * based on ezSql_mssql library class.
 *
 * @author  Muhammad Iyas (iyasilias@gmail.com)
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @link
 * @name    ezSQL_sybase
 * @package ezSQL
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 *
 */
class ezSQL_sybase extends ezSQLcore
{
    /**
     * ezSQL error strings - Sybase ASE
     * @var array
     */
    private $ezsql_sybase_str = array
        (
                1 => 'Require $dbuser and $dbpassword to connect to a database server',
                2 => 'Error establishing sybase database connection. Correct user/password? Correct hostname? Database server running?',
                3 => 'Require $dbname to select a database',
                4 => 'SQL Server database connection is not active',
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
     * Show errors
     * @var boolean Default is true
     */
    public $show_errors = true;

    /**
     * if we want to convert Queries in MySql syntax to Sybase syntax. Yes,
     * there are some differences in query syntax.
     * @var boolean Default is true
     */
    private $convertMySqlToSybaseQuery = true;

    /**********************************************************************
    *  Constructor - allow the user to perform a qucik connect at the
    *  same time as initialising the ezSQL_sybase class
    */

    /**
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     * @param string $dbname The name of the database
     *                       Default is empty string
     * @param string $dbhost The host name or IP address of the database server.
     *                       Default is localhost
     * @param boolean $convertMySqlToSybaseQuery Default is true
     * @throws Exception Requires ntwdblib.dll and ez_sql_core.php
     */
    public function __construct($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $convertMySqlToSybaseQuery=true) {
        if ( ! function_exists ('sybase_connect') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_sybase requires ntwdblib.dll to be present in your winowds\system32 folder. Also enable sybase extenstion in PHP.ini file ');
        }
        if ( ! class_exists ('ezSQLcore') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_sybase requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');
        }

        sybase_min_server_severity(20);
        parent::__construct();

        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbname = $dbname;
        $this->dbhost = $dbhost;
        $this->convertMySqlToSybaseQuery = $convertMySqlToSybaseQuery;
    } // __construct

    /**
     * Short hand way to connect to sybase database server and select a sybase
     * database at the same time
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     * @param string $dbname The name of the database
     *                       Default is empty string
     * @param string $dbhost The host name or IP address of the database server.
     *                       Default is localhost
     * @return boolean
     */
    public function quick_connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost') {
        if ( ! $this->connect($dbuser, $dbpassword, $dbhost,true) ) ;
        else if ( ! $this->select($dbname) ) ;

        return $this->connected;
    } // quick_connect

    /**
     * Try to connect to sybase database server
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     * @param string $dbhost The host name or IP address of the database server.
     *                       Default is localhost
     * @return boolean
     */
    public function connect($dbuser='', $dbpassword='', $dbhost='localhost') {
        $this->connected = false;

        // Must have a user and a password
        if ( ! $dbuser ) {
            $this->register_error($this->ezsql_sybase_str[1] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_sybase_str[1], E_USER_WARNING) : null;
        } else if ( ! $this->dbh = @sybase_connect($dbhost, $dbuser, $dbpassword) ) {
            // Try to establish the server database handle
            $this->register_error($this->ezsql_sybase_str[2] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_sybase_str[2], E_USER_WARNING) : null;
        } else {
            $this->dbuser = $dbuser;
            $this->dbpassword = $dbpassword;
            $this->dbhost = $dbhost;
            $this->connected = true;
        }

        return $this->connected;
    } // connect

    /**********************************************************************
    *
    */

    /**
     * Try to select a sybase database
     *
     * @param string $dbname
     * @return boolean
     */
    public function select($dbname='') {
        $this->connected = false;

        // Must have a database name
        if ( ! $dbname ) {
            $this->register_error($this->ezsql_sybase_str[3] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_sybase_str[3], E_USER_WARNING) : null;
        } else if ( ! $this->dbh ) {
            // Must have an active database connection
            $this->register_error($this->ezsql_sybase_str[4] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_sybase_str[4], E_USER_WARNING) : null;
        } else if ( !@sybase_select_db($dbname,$this->dbh) ) {
            // Try to connect to the database
            $str = $ezsql_sybase_str[5];

            $this->register_error($str . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($str, E_USER_WARNING) : null;
        } else {
            $this->dbname = $dbname;
            $this->connected = true;
        }

        return $this->connected;
    } // select

    /**
     * Format a sybase string correctly for safe sybase insert
     *
     * @param string $str
     * @return string
     */
    public function escape($str) {
        $return_val = '';

        if ( !isset($str) or empty($str) ) {
            $return_val = '';
        } else if ( is_numeric($str) ) {
            $return_val = $str;
        } else {
            $non_displayables = array(
                '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
                '/%1[0-9a-f]/',             // url encoded 16-31
                '/[\x00-\x08]/',            // 00-08
                '/\x0b/',                   // 11
                '/\x0c/',                   // 12
                '/[\x0e-\x1f]/'             // 14-31
            );

            foreach ( $non_displayables as $regex ) {
                $str = preg_replace( $regex, '', $str );
            }

            $return_val = str_replace("'", "''", $str );
        }

        return $return_val;
    } // escape

    /**
     * Return sybase specific system date syntax
     * i.e. Oracle: SYSDATE sybase: getDate()
     *
     * @return string
     */
    public function sysdate() {
        return 'getDate()';
    } // sysdate

    /**
     * Perform sybase query and try to detirmin result value
     *
     * @param string $query
     * @return object
     */
    public function query($query) {
        // If flag to convert query from MySql syntax to Sybase syntax is true
        // Convert the query
        if($this->convertMySqlTosybaseQuery == true) {
            $query = $this->ConvertMySqlTosybase($query);
        }

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


        // Perform the query via std sybase_query function..
        $this->result = @sybase_query($query);

        // If there is an error then take note of it..
        if ($this->result == false ) {

            $get_errorcodeSql = "SELECT @@ERROR as errorcode";
            $error_res = @sybase_query($get_errorcodeSql, $this->dbh);
            $errorCode = @sybase_result($error_res, 0, 'errorcode');

            $get_errorMessageSql = 'SELECT severity as errorSeverity, text as errorText FROM sys.messages  WHERE message_id = ' . $errorCode;
            $errormessage_res =  @sybase_query($get_errorMessageSql, $this->dbh);
            if($errormessage_res) {
                $errorMessage_Row = @sybase_fetch_row($errormessage_res);
                $errorSeverity = $errorMessage_Row[0];
                $errorMessage = $errorMessage_Row[1];
            }

            $sqlError = 'ErrorCode: ' . $errorCode. ' ### Error Severity: ' . $errorSeverity . ' ### Error Message: ' . $errorMessage.' ### Query: ' . $query;

            $is_insert = true;
            $this->register_error($sqlError);
            $this->show_errors ? trigger_error($sqlError, E_USER_WARNING) : null;
            return false;
        }

        // Query was an insert, delete, update, replace
        $is_insert = false;
        if ( preg_match("/^(insert|delete|update|replace)\s+/i", $query) ) {
            $this->rows_affected = @sybase_rows_affected($this->dbh);

            // Take note of the insert_id
            if ( preg_match("/^(insert|replace)\s+/i",$query) ) {

                $identityresultset = @sybase_query('select SCOPE_IDENTITY()');

                if ($identityresultset != false ) {
                    $identityrow = @sybase_fetch_row($identityresultset);
                    $this->insert_id = $identityrow[0];
                }
            }

            // Return number of rows affected
            $return_val = $this->rows_affected;
        } else {
            // Query was a select
            // Take note of column info
            $i=0;
            while ($i < @sybase_num_fields($this->result)) {
                $this->col_info[$i] = @sybase_fetch_field($this->result);
                $i++;
            }

            // Store Query Results
            $num_rows=0;

            while ( $row = @sybase_fetch_object($this->result) ) {
                // Store relults as an objects within main array
                $this->last_result[$num_rows] = $row;
                $num_rows++;
            }

            @sybase_free_result($this->result);

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
     * Convert a Query From MySql Syntax to Sybase syntax
     * Following conversions are made:
     * 1. The '`' character used for MySql queries is not supported - the
     *    character is removed.
     * 2. FROM_UNIXTIME method is not supported. The Function is removed.It is
     *    replaced with getDate(). Warning: This logic may not be right.
     * 3. unix_timestamp function is removed.
     * 4. LIMIT keyowrd is replaced with TOP keyword. Warning: Logic not fully
     *    tested.
     *
     * Note: This method is only a small attempt to convert the syntax. There
     *       are many aspects which are not covered here. This method doesn't at
     *       all guarantee complete conversion. Certain queries will still not
     *       work.
     *
     * @param string $query
     * @return string
     */
    public function ConvertMySqlTosybase($query) {
        //replace the '`' character used for MySql queries, but not
        //supported in Sybase

        $query = str_replace('`', '', $query);

        //replace From UnixTime command in Sybase, doesn't work

        $pattern = "/FROM_UNIXTIME\(([^\/]{0,})\)/i";
        $replacement = 'getdate()';
        //ereg($pattern, $query, $regs);
        //we can get the Unix Time function parameter value from this string
        //$valueInsideFromUnixTime = $regs[1];

        $query = preg_replace($pattern, $replacement, $query);

        //replace LIMIT keyword. Works only on MySql not on Sybase
        //replace it with TOP keyword

        $pattern = "/LIMIT[^\w]{1,}([0-9]{1,})([\,]{0,})([0-9]{0,})/i";
        $replacement = '';
        preg_match($pattern, $query, $regs);

        $query = preg_replace($pattern, $replacement, $query);

        if(count($regs) > 0) {
            if($regs[2]) {
                $query = str_ireplace('SELECT ', 'SELECT TOP ' . $regs[3] . ' ', $query);
            } else if($regs[1]) {
                $query = str_ireplace('SELECT ', 'SELECT TOP ' . $regs[1] . ' ', $query);
            }
        }

        //replace unix_timestamp function. Doesn't work in Sybase
        $pattern = "/unix_timestamp\(([^\/]{0,})\)/i";
        $replacement = "\\1";
        $query = preg_replace($pattern, $replacement, $query);

        return $query;
    }

    /**
     * Close the database connection 
     */
    public function disconnect() {
        if ( $this->dbh ) {
            $this->dbh = null;
            $this->connected = false;
        }
    } // disconnect

    /**
     * Returns the current database server host
     *
     * @return string
     */
    public function getDBHost() {
        return $this->dbhost;
    } // getDBHost

} // ezSQL_sybase