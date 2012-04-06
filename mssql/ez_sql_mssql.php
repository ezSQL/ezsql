<?php
/**
 * ezSQL Database specific class - mssql
 * Microsoft Sql Server component (part of ezSQL databse abstraction library) -
 * based on ezSql_mySql library class.
 *
 * @author  ashank (ashank@gmail.com)
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @link    http://twitter.com/justinvincent
 * @name    ezSQL_mssql
 * @package ezSQL
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 */
class ezSQL_mssql extends ezSQLcore
{
    /**
     * ezSQL error strings - mssql
     * @var array Default 5 error messages
     */
    private $ezsql_mssql_str = array
        (
            1 => 'Require $dbuser and $dbpassword to connect to a database server',
            2 => 'Error establishing mssql database connection. Correct user/password? Correct hostname? Database server running?',
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
     * If we want to convert Queries in MySql syntax to MS-SQL syntax. Yes,
     * there are some differences in query syntax.
     * @var boolean Default is true
     */
    private $convertMySqlToMSSqlQuery = true;

    /**
     * Constructor - allow the user to perform a qucik connect at the same time
     * as initialising the ezSQL_mssql class
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     * @param string $dbname The name of the database
     *                       Default is empty string
     * @param string $dbhost The host name or IP address of the database server.
     *                       Default is localhost
     * @param boolean $convertMySqlToMSSqlQuery Default is true
     * @throws Exception Requires ntwdblib.dll and ez_sql_core.php
     */
    public function __construct($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $convertMySqlToMSSqlQuery=true) {
        if ( ! function_exists ('mssql_connect') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_mssql requires ntwdblib.dll to be present in your winowds\system32 folder. Also enable MS-SQL extenstion in PHP.ini file ');
        }
        if ( ! class_exists ('ezSQLcore') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_mssql requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');
        }

        parent::__construct();

        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbname = $dbname;
        $this->dbhost = $dbhost;
        $this->convertMySqlToMSSqlQuery = $convertMySqlToMSSqlQuery;
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
        if ( ! $this->connect($dbuser, $dbpassword, $dbhost) ) ;
        else if ( ! $this->select($dbname) );

        return $this->connected;
    } // quick_connect

    /**
     * Try to connect to mssql database server
     *
     * @param string $dbuser The database user name
     * @param string $dbpassword The database users password
     * @param string $dbhost The host name or IP address of the database server.
     *                       Default is localhost
     * @return boolean
     */
    public function connect($dbuser='', $dbpassword='', $dbhost='localhost') {
        $this->connected = false;

        // Must have a user and a password
        if ( ! $dbuser )
        {
            $this->register_error($this->ezsql_mssql_str[1] . ' in ' . __FILE__ . ' on line ' .__LINE__);
            $this->show_errors ? trigger_error($this->ezsql_mssql_str[1], E_USER_WARNING) : null;
        } else if ( ! $this->dbh = @mssql_connect($dbhost, $dbuser, $dbpassword) ) {
            // Try to establish the server database handle
            $this->register_error($this->ezsql_mssql_str[2] . ' in ' .__FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_mssql_str[2], E_USER_WARNING) : null;
        } else {
            $this->dbuser = $dbuser;
            $this->dbpassword = $dbpassword;
            $this->dbhost = $dbhost;
            $this->connected = true;
        }

        return $this->connected;
    } // connect

    /**
     * Try to select a mssql database
     *
     * @param string $dbname The name of the database
     * @return boolean
     */
    public function select($dbname='') {
        if ( ! $dbname ) {
            // Must have a database name
            $this->register_error($this->ezsql_mssql_str[3] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_mssql_str[3], E_USER_WARNING) : null;
        } else if ( ! $this->dbh ) {
            // Must have an active database connection
            $this->register_error($this->ezsql_mssql_str[4] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_mssql_str[4], E_USER_WARNING) : null;
        } else if ( !@mssql_select_db($dbname,$this->dbh) ) {
            // Try to connect to the database
            $str = $this->ezsql_mssql_str[5];

            $this->register_error($str . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($str, E_USER_WARNING) : null;
        } else {
            $this->dbname = $dbname;
            $this->connected = true;
        }

        return $this->connected;
    } // select

    /**
     * Format a mssql string correctly for safe mssql insert
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
     * Return mssql specific system date syntax
     * i.e. Oracle: SYSDATE mssql: NOW(), MS-SQL : getDate()
     *
     * @return string
     */
    public function sysdate() {
        return 'getDate()';
    } // sysdate


   /**
     * Perform the mssql query and try to determine the result value
     *
     * @param string $query
     * @return boolean
     */
   public function query($query) {

        // If flag to convert query from MySql syntax to MS-Sql syntax is true
        // convert the query
        if($this->convertMySqlToMSSqlQuery == true) {
            $query = $this->ConvertMySqlToMSSql($query);
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

        // Perform the query via std mssql_query function..

        $this->result = @mssql_query($query);

        // If there is an error then take note of it..
        if ($this->result == false ) {

            $get_errorcodeSql = "SELECT @@ERROR as errorcode";
            $error_res = @mssql_query($get_errorcodeSql, $this->dbh);
            $errorCode = @mssql_result($error_res, 0, 'errorcode');

            $get_errorMessageSql = "SELECT severity as errorSeverity, text as errorText FROM sys.messages  WHERE message_id = ".$errorCode  ;
            $errormessage_res =  @mssql_query($get_errorMessageSql, $this->dbh);
            
            if($errormessage_res) {
                $errorMessage_Row = @mssql_fetch_row($errormessage_res);
                $errorSeverity = $errorMessage_Row[0];
                $errorMessage = $errorMessage_Row[1];
            }

            $sqlError = "ErrorCode: " . $errorCode . " ### Error Severity: " . $errorSeverity . " ### Error Message: ".$errorMessage." ### Query: " . $query;

            $is_insert = true;
            $this->register_error($sqlError);
            $this->show_errors ? trigger_error($sqlError, E_USER_WARNING) : null;
            return false;
        }

        // Query was an insert, delete, update, replace
        $is_insert = false;
        if ( preg_match("/^(insert|delete|update|replace)\s+/i",$query) ) {
            $this->rows_affected = @mssql_rows_affected($this->dbh);

            // Take note of the insert_id
            if ( preg_match("/^(insert|replace)\s+/i",$query) ) {

                $identityresultset = @mssql_query("select SCOPE_IDENTITY()");

                if ($identityresultset != false) {
                    $identityrow = @mssql_fetch_row($identityresultset);
                    $this->insert_id = $identityrow[0];
                }
            }

            // Return number of rows affected
            $return_val = $this->rows_affected;
        } else {
            // Query was a select

            // Take note of column info
            $i=0;
            while ($i < @mssql_num_fields($this->result)) {
                $this->col_info[$i] = @mssql_fetch_field($this->result);
                $i++;
            }

            // Store Query Results
            $num_rows=0;

            while ( $row = @mssql_fetch_object($this->result) ) {
                // Store relults as an objects within main array
                $this->last_result[$num_rows] = $row;
                $num_rows++;
            }

            @mssql_free_result($this->result);

            // Log number of rows the query returned
            $this->num_rows = $num_rows;

            // Return number of rows selected
            $return_val = $this->num_rows;
        }

        // disk caching of queries
        $this->store_cache($query,$is_insert);

        // If debug ALL queries
        $this->trace || $this->debug_all ? $this->debug() : null ;

        return $return_val;
    } // query

    /**
     * Convert a Query From MySql Syntax to MS-Sql syntax
     * Following conversions are made:
     * 1. The '`' character used for MySql queries is not supported -
     *    the character is removed.
     * 2. FROM_UNIXTIME method is not supported. The Function is removed.It is
     *    replaced with getDate(). Warning: This logic may not be right.
     * 3. unix_timestamp function is removed.
     * 4. LIMIT keyowrd is replaced with TOP keyword. Warning: Logic not fully
     *    tested.
     *
     * Note: This method is only a small attempt to convert the syntax. There
     *       are many aspects which are not covered here.
     *       This method doesn't at all guarantee complete conversion. Certain
     *       queries will still not work. e.g. MS SQL requires all columns in
     *       Select Clause to be present in 'group by' clause.
     *       There is no such restriction in MySql.
     *
     * @param string $query
     * @return string
     */
    public function ConvertMySqlToMSSql($query) {
        // replace the '`' character used for MySql queries, but not
        // supported in MS-Sql

        $query = str_replace('`', '', $query);

        // replace From UnixTime command in MS-Sql, doesn't work
        $pattern = "FROM_UNIXTIME\(([^/]{0,})\)";
        $replacement = 'getdate()';
        //ereg($pattern, $query, $regs);
        //we can get the Unix Time function parameter value from this string
        //$valueInsideFromUnixTime = $regs[1];

        $query = eregi_replace($pattern, $replacement, $query);

        // replace LIMIT keyword. Works only on MySql not on MS-Sql
        // replace it with TOP keyword
        $pattern = "LIMIT[^\w]{1,}([0-9]{1,})([\,]{0,})([0-9]{0,})";
        $replacement = '';
        eregi($pattern, $query, $regs);
        $query = eregi_replace($pattern, $replacement, $query);

        if ( $regs[2] ) {
            $query = str_ireplace('SELECT ', 'SELECT TOP ' . $regs[3] . ' ', $query);
        } else if ( $regs[1] ) {
            $query  = str_ireplace('SELECT ', 'SELECT TOP ' . $regs[1] . ' ', $query);
        }

        //replace unix_timestamp function. Doesn't work in MS-Sql
        $pattern = "unix_timestamp\(([^/]{0,})\)";
        $replacement = "\\1";
        $query = eregi_replace($pattern, $replacement, $query);

        return $query;
    } // ConvertMySqlToMSSql

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

} // ezSQL_mssql