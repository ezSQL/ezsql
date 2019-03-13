<?php

class ezSQL_oracleTNS extends ezSQLcore
{
    /**
     * ezSQL error strings - Oracle 8 and 9
     * @var array
     */
    private $_ezsql_oracle_str = array
        (
            1 => 'Require $dbuser, $dbpassword and $dbname to connect to a database server',
            2 => 'ezSQL auto created the following Oracle sequence'
        );

    /**
     * Database user name
     * @var string
     */
    private $_dbuser;

    /**
     * Database password for the given user
     * @var string
     */
    private $_dbpassword;

    /**
     * Database server name or IP address
     * @var string
     */
    private $_host;

    /**
     * TCP port for the database connection on the specified server
     * @var integer
     */
    private $_port;

    /**
     * The service name
     * @var string
     */
    private $_serviceName;

    /**
     * The connection string
     * @var string
     */
    private $_tns;

    /**
     * The Oracle NLS_LANG character set for the connection
     * Default: Empty string
     * @var string
     */
    private $_characterSet;
    
    /**
     * Use oci_pconnect instead of oci_connect to have connection pooling
     * enabled with PHP
     * @var boolean
     */
    private $_pooling;
    
    /**
     * Show errors
     * @var boolean Default is true
     */
    public $show_errors = true;


    /**
     * Constructor - allow the user to perform a qucik connect at the same time
     * as initialising the ezSQL_oracleTNS class
     *
     * @param string $host The server name or the IP address of the server
     * @param integer $port The TCP port of the server
     * @param string $serviceName The service name
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     * @param string $characterSet The Oracle NLS_LANG character string
     *                             Default is empty string
     * @param boolean $pooling Use connection pooling with pconnect instead of
     *                         connect
     *                         Default is false
     * @throws Exception Requires Orcle OCI Lib and ez_sql_core.php
     */
    public function __construct($host, $port, $serviceName, $dbuser='', $dbpassword='', $characterSet='', $pooling=false) {
        if ( ! function_exists ('OCILogon') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_oracleTNS requires Oracle OCI Lib to be compiled and/or linked in to the PHP engine');
        }
        if ( ! class_exists ('ezSQLcore') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL_oracle8_9 requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');
        }

        parent::__construct();

        // Turn on track errors
        ini_set('track_errors',1);

        $this->_dbuser = $dbuser;
        $this->_dbpassword = $dbpassword;
        $this->_host = $host;
        $this->_port = $port;
        $this->_serviceName = $serviceName;
        $this->_characterSet = $characterSet;
        $this->setTNS();
        $this->_pooling = $pooling;
        
        global $_ezOracleTNS;
        $_ezOracleTNS = $this;
    } // __construct

    /**
     * Try to connect to Oracle database server
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     * @return boolean
     */
    public function connect($dbuser='', $dbpassword='') {
        $this->_connected = false;
        
        if (empty($dbuser)) {
            $dbuser = $this->_dbuser;
        }
        if (empty($dbpassword)) {
            $dbpassword = $this->_dbpassword;
        }

        // Must have a user and a password
        if ( ! $dbuser || ! $dbpassword) {
            $this->register_error($this->_ezsql_oracle_str[1] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->_ezsql_oracle_str[1], E_USER_WARNING) : null;
        }

        // Try to establish the server database handle
        else {
                if ($this->_pooling) {
                    $this->_pconnect($dbuser, $dbpassword);
                }  else {
                    $this->_connect($dbuser, $dbpassword);
                }

            }

        return $this->_connected;
    } // connect

    /**
     * Try to connect to Oracle database server with connection pooling
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     * @return boolean
     */
    public function pconnect($dbuser='', $dbpassword='') {
        $this->_connected = false;
        
        if (empty($dbuser)) {
            $dbuser = $this->_dbuser;
        }
        if (empty($dbpassword)) {
            $dbpassword = $this->_dbpassword;
        }

        // Must have a user and a password
        if ( ! $dbuser || ! $dbpassword) {
            $this->register_error($this->_ezsql_oracle_str[1] . ' in ' . __FILE__ . ' on line ' . __LINE__);
            $this->show_errors ? trigger_error($this->_ezsql_oracle_str[1], E_USER_WARNING) : null;
        }

        // Try to establish the server database handle
        else {
                $this->_pconnect($dbuser, $dbpassword);
            }

        return $this->_connected;
    } // pconnect

    /**
     * Try to connect to Oracle database server without connection pooling
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     */
    private function _connect($dbuser='', $dbpassword='') {
        if ( ! empty($this->_characterSet) ) {
                if ( ! $this->dbh = @oci_connect($dbuser, $dbpassword, $this->_tns, $this->_characterSet) ) {
                    $this->register_error($php_errormsg);
                    $this->show_errors ? trigger_error($php_errormsg,E_USER_WARNING) : null;
                } else {
                    $this->_dbuser = $dbuser;
                    $this->_dbpassword = $dbpassword;
                    $this->_connected = true;
                }
        } else {
                if ( ! $this->dbh = @oci_connect($dbuser, $dbpassword, $this->_tns) ) {
                    $this->register_error($php_errormsg);
                    $this->show_errors ? trigger_error($php_errormsg,E_USER_WARNING) : null;
                } else {
                    $this->_dbuser = $dbuser;
                    $this->_dbpassword = $dbpassword;
                    $this->_connected = true;
                }
            }
    }
    
    /**
     * Try to connect to Oracle database server with connection pooling
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     */
    private function _pconnect($dbuser='', $dbpassword='') {
        if ( ! empty($this->_characterSet) ) {
                if ( ! $this->dbh = @oci_pconnect($dbuser, $dbpassword, $this->_tns, $this->_characterSet) ) {
                    $this->register_error($php_errormsg);
                    $this->show_errors ? trigger_error($php_errormsg,E_USER_WARNING) : null;
                } else {
                    $this->_dbuser = $dbuser;
                    $this->_dbpassword = $dbpassword;
                    $this->_connected = true;
                }
        } else {
                if ( ! $this->dbh = @oci_pconnect($dbuser, $dbpassword, $this->_tns) ) {
                    $this->register_error($php_errormsg);
                    $this->show_errors ? trigger_error($php_errormsg,E_USER_WARNING) : null;
                } else {
                    $this->_dbuser = $dbuser;
                    $this->_dbpassword = $dbpassword;
                    $this->_connected = true;
                }
            }
    } // _connect
    
    /**
     * In the case of Oracle quick_connect is not really needed because std.
     * connect already does what quick connect does - but for the sake of
     * consistency it has been included
     *
     * @param string $dbuser The database user name
     *                       Default is empty string
     * @param string $dbpassword The database users password
     *                           Default is empty string
     * @return boolean
     */
    public function quick_connect($dbuser='', $dbpassword='') {
        return $this->connect($dbuser, $dbpassword);
    } // quick_connect

    /**
     * Format a Oracle string correctly for safe Oracle insert
     *
     * @param string $str
     * @return string
     */
    public function escape($str) {
        if ( !isset($str) ) return '';
        if ( is_numeric($str) ) return $str;

        $non_displayables = array(
                '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
                '/%1[0-9a-f]/',             // url encoded 16-31
                '/[\x00-\x08]/',            // 00-08
                '/\x0b/',                   // 11
                '/\x0c/',                   // 12
                '/[\x0e-\x1f]/'             // 14-31
                );
                
        foreach ( $non_displayables as $regex )
            $str = preg_replace( $regex, '', $str );
        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

        return str_replace($search, $replace, $str);
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
            $this->register_error($this->_ezsql_oracle_str[2] . ": $seq_name");
            $this->show_errors ? trigger_error($this->_ezsql_oracle_str[2] . ": $seq_name", E_USER_NOTICE) : null;
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
            $this->connect($this->_dbuser, $this->_dbpassword);
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
            $this->_affectedRows = @OCIRowCount($stmt);
            $return_value = $this->_affectedRows;
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
            oci_close($this->dbh);
            $this->_connected = false;
        }
    } // disconnect

    /**
     * Sets the TNS variable with all relevant connection informations
     */
    private function setTNS() {
        $this->_tns = "(DESCRIPTION =
            (ADDRESS=(PROTOCOL = TCP)(HOST = $this->_host)(PORT = $this->_port))
            (CONNECT_DATA=(SERVER = DEDICATED)(SERVICE_NAME = $this->_serviceName)))";
    } // setTNS

} // ezSQL_oracle8_9