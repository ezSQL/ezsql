<?php
/**
 * ezSQL Database specific class - Oracle 8 and 9
 * Desc..: Oracle 8i/9i component (part of ezSQL databse abstraction library)
 *
 * @author  Justin Vincent (jv@jvmultimedia.com)
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @link    http://twitter.com/justinvincent
 * @name    ezSQL_oracle8_9
 * @package ezSQL
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 *
 */
class ezSQL_oracle8_9 extends ezSQLcore
{
    /**
     * ezSQL error strings - Oracle 8 and 9
     * @var array
     */
    private $ezsql_oracle8_9_str = array
        (
            1 => 'Require $dbuser, $dbpassword and $dbname to connect to a database server',
            2 => 'ezSQL auto created the following Oracle sequence'
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
     * Show errors
     * @var boolean Default is true
     */
    public $show_errors = true;

    /**
     * Constructor - allow the user to perform a qucik connect at the same time
     * as initialising the ezSQL_oracle8_9 class
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     * @param string $dbname The name of the database
     *                       Default is empty string
     * @throws Exception Requires Orcle OCI Lib and ez_sql_core.php
     */
    public function __construct($dbuser='', $dbpassword='', $dbname='') {
        if ( ! function_exists ('OCILogon') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_oracle8_9 requires Oracle OCI Lib to be compiled and/or linked in to the PHP engine');
        }
        if ( ! class_exists ('ezSQLcore') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_oracle8_9 requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');
        }

        parent::__construct();

        // Turn on track errors
        ini_set('track_errors',1);

        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbname = $dbname;

    } // __construct

    /**
     * Try to connect to Oracle database server
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     * @param string $dbname The name of the database
     *                       Default is empty string
     * @return boolean
     */
    public function connect($dbuser='', $dbpassword='', $dbname='') {
        $this->connected = false;

        // Must have a user and a password
        if ( ! $dbuser || ! $dbpassword || ! $dbname ) {
            $this->register_error($this->ezsql_oracle8_9_str[1] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->ezsql_oracle8_9_str[1], E_USER_WARNING) : null;
        }
        // Try to establish the server database handle
        else if ( ! $this->dbh = OCILogon($dbuser, $dbpassword, $dbname) )
        {
            $this->register_error($php_errormsg);
            $this->show_errors ? trigger_error($php_errormsg,E_USER_WARNING) : null;
        } else {
            $this->dbuser = $dbuser;
            $this->dbpassword = $dbpassword;
            $this->dbname = $dbname;
            $this->connected = true;
        }

        return $this->connected;
    }

    /**
     * In the case of Oracle quick_connect is not really needed because std.
     * connect already does what quick connect does - but for the sake of
     * consistency it has been included
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     * @param string $dbname The name of the database
     *                       Default is empty string
     * @return boolean
     */
    public function quick_connect($dbuser='', $dbpassword='', $dbname='') {
        return $this->connect($dbuser, $dbpassword, $dbname);
    } // quick_connect

    /**
     * No real equivalent of mySQL select in Oracle, once again, function
     * included for the sake of consistency
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     * @param string $dbname The name of the database
     *                       Default is empty string
     * @return boolean
     */
    public function select($dbuser='', $dbpassword='', $dbname='') {
        return $this->connect($dbuser, $dbpassword, $dbname);
    } // select

    /**
     * Format a Oracle string correctly for safe Oracle insert
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
     * Return Oracle specific system date syntax
     * i.e. Oracle: SYSDATE Mysql: NOW()
     *
     * @return string
     */
    public function sysdate() {
        return 'SYSDATE';
    } // sysdate

    /**********************************************************************
    *  These special Oracle functions make sure that even if your test
    *  pattern is '' it will still match records that are null if
    *  you don't use these funcs then oracle will return no results
    *  if $user = ''; even if there were records that = ''
    *
    *  SELECT * FROM USERS WHERE USER = ".$db->is_equal_str($user)."
    */

    /**
     * Returns an escaped equal string
     *
     * @param string $str
     * @return string
     */
    public function is_equal_str($str='') {
        return ($str=='' ? 'IS NULL' : "= '" . $this->escape($str) . "'");
    } // is_equal_str

    /**
     * Returns an equal string for integer values
     *
     * @param string $int
     * @return string
     */
    public function is_equal_int($int) {
        return ($int=='' ? 'IS NULL': '= ' . $int);
    } // is_equal_int

    /**
     * Another oracle specific function - if you have set up a sequence this
     * function returns the next ID from that sequence
     * If the sequence is not defined, the sequence is created by this method.
     * Though be shure, that you use the correct sequence name not to end in
     * more than one sequence for a primary key of a table.
     *
     * @param string $seq_name Name of the sequenze
     * @return string
     */
    public function insert_id($seq_name) {
        $return_val = $this->get_var("SELECT $seq_name.nextVal id FROM Dual");

        // If no return value then try to create the sequence
        if ( ! $return_val ) {
            $this->query("CREATE SEQUENCE $seq_name maxValue 9999999999 INCREMENT BY 1 START WITH 1 CACHE 20 CYCLE");
            $return_val = $this->get_var("SELECT $seq_name.nextVal id FROM Dual");
            $this->register_error($this->ezsql_oracle8_9_str[2] . ": $seq_name");
            $this->show_errors ? trigger_error($this->ezsql_oracle8_9_str[2] . ": $seq_name", E_USER_NOTICE) : null;
        }

        return $return_val;
    } // insert_id

    /**
     * An alias for insert_id using the original Oracle function name.
     *
     * @param string $seq_name Name of the sequenze
     * @return string
     */
    public function nextVal($seq_name) {
        return $this->insert_id($seq_name);
    } // nextVal

    /**
     * Perform Oracle query and try to determine result value
     *
     * @param string $query
     * @return object
     */
    public function query($query) {
        $return_value = 0;

        // Flush cached values..
        $this->flush();

        // Log how the function was called
        $this->func_call = "\$db->query(\"$query\")";

        // Keep track of the last query for debug..
        $this->last_query = $query;

        $this->num_queries++;

        // Use core file cache function
        if ( $cache = $this->get_cache($query) ) {
            return $cache;
        }

        // If there is no existing database connection then try to connect
        if ( ! isset($this->dbh) || ! $this->dbh ) {
            $this->connect($this->dbuser, $this->dbpassword, $this->dbname);
        }

        // Parses the query and returns a statement..
        if ( ! $stmt = OCIParse($this->dbh, $query)) {
            $error = OCIError($this->dbh);
            $this->register_error($error['message']);
            $this->show_errors ? trigger_error($error['message'], E_USER_WARNING) : null;
            return false;
        } elseif ( ! $this->result = OCIExecute($stmt)) {
            // Execut the query..
            $error = OCIError($stmt);
            $this->register_error($error['message']);
            $this->show_errors ? trigger_error($error['message'], E_USER_WARNING) : null;
            return false;
        }

        // If query was an insert
        $is_insert = false;
        if ( preg_match('/^(insert|delete|update|create) /i', $query) ) {
            $is_insert = true;

            // num afected rows
            $return_value = $this->rows_affected = @OCIRowCount($stmt);
        } else {
            // If query was a select
            // Get column information
            if ( $num_cols = @OCINumCols($stmt) ) {
                // Fetch the column meta data
                for ( $i = 1; $i <= $num_cols; $i++ ) {
                    $this->col_info[($i-1)]->name = @OCIColumnName($stmt, $i);
                    $this->col_info[($i-1)]->type = @OCIColumnType($stmt, $i);
                    $this->col_info[($i-1)]->size = @OCIColumnSize($stmt, $i);
                }
            }

            // If there are any results then get them
            if ($this->num_rows = @OCIFetchStatement($stmt, $results)) {
                // Convert results into object orientated results..
                // Due to Oracle strange return structure - loop through columns
                foreach ( $results as $col_title => $col_contents ) {
                    $row_num=0;
                    // Then - loop through rows
                    foreach (  $col_contents as $col_content ) {
                        $this->last_result[$row_num]->{$col_title} = $col_content;
                        $row_num++;
                    }
                }
            }

            // Num result rows
            $return_value = $this->num_rows;
        }

        // Disk caching of queries
        $this->store_cache($query, $is_insert);

        // If debug ALL queries
        $this->trace || $this->debug_all ? $this->debug() : null;

        return $return_value;
    } // query

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
     * Returns the current database name
     *
     * @return string
     */
    public function getDBName() {
        return $this->dbname;
    } // getDBName

} // ezSQL_oracle8_9