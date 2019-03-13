<?php

	/**********************************************************************
	*  ezSQL error strings - Oracle
	*/

	global $ezsql_oracle8_9_str;
	
	$ezsql_oracle8_9_str = array
	(
		1 => 'Require $dbuser, $dbpassword and $dbname to connect to a database server',
		2 => 'ezSQL auto created the following Oracle sequence'
	);

	/**********************************************************************
	*  ezSQL Database specific class - Oracle
	*/

	if ( ! function_exists ('oci_connect') ) die('<b>Fatal Error:</b> ezSQL_oracle8_9 requires Oracle OCI Lib to be compiled and/or linked in to the PHP engine');
	if ( ! class_exists ('ezSQLcore') ) die('<b>Fatal Error:</b> ezSQL_oracle8_9 requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');

	class ezSQL_oracle8_9 extends ezSQLcore
	{

		var $dbuser = false;
		var $dbpassword = false;
		var $dbname = false;
		var $rows_affected = false;

		/**********************************************************************
		*  Constructor - allow the user to perform a quick connect at the
		*  same time as initializing the ezSQL_oracle8_9 class
		*/

		function __construct($dbuser='', $dbpassword='', $dbname='')
		{

			// Turn on track errors
			ini_set('track_errors',1);

			$this->dbuser = $dbuser;
			$this->dbpassword = $dbpassword;
			$this->dbname = $dbname;

            global $_ezOracle8_9;
            $_ezOracle8_9 = $this;
		}

		/**********************************************************************
		*  Try to connect to Oracle database server
		*/

		function connect($dbuser='', $dbpassword='', $dbname='')
		{
			global $ezsql_oracle8_9_str; $return_val = false;
            $this->_connected = false;

			// Must have a user and a password
			if ( ! $dbuser || ! $dbpassword || ! $dbname )
			{
				$this->register_error($ezsql_oracle8_9_str[1].' in '.__FILE__.' on line '.__LINE__);
				$this->show_errors ? trigger_error($ezsql_oracle8_9_str[1],E_USER_WARNING) : null;
			}
			// Try to establish the server database handle
			else if ( ! $this->dbh = oci_connect($dbuser, $dbpassword, $dbname) )
			{
				$this->register_error($php_errormsg);
				$this->show_errors ? trigger_error($php_errormsg,E_USER_WARNING) : null;
			}
			else
			{
				$this->dbuser = $dbuser;
				$this->dbpassword = $dbpassword;
				$this->dbname = $dbname;
				$return_val = true;
                $this->_connected = true;

				$this->conn_queries = 0;
			}
            
			return $return_val;
		}

		/**********************************************************************
		*  In the case of Oracle quick_connect is not really needed
		*  because std. connect already does what quick connect does -
		*  but for the sake of consistency it has been included
		*/

		function quick_connect($dbuser='', $dbpassword='', $dbname='')
		{
			return $this->connect($dbuser='', $dbpassword='', $dbname='');
		}

		/**********************************************************************
		*  Return Oracle specific system date syntax
		*  i.e. Oracle: SYSDATE Mysql: NOW()
		*/

		function sysdate()
		{
			return "SYSDATE";
		}

		/**********************************************************************
		*  These special Oracle functions make sure that even if your test
		*  pattern is '' it will still match records that are null if
		*  you don't use these funcs then oracle will return no results
		*  if $user = ''; even if there were records that = ''
		*
		*  SELECT * FROM USERS WHERE USER = ".$db->is_equal_str($user)."
		*/

		function is_equal_str($str='')
		{
			return ($str==''?'IS NULL':"= '".$this->escape($str)."'");
		}

		function is_equal_int($int)
		{
			return ($int==''?'IS NULL':'= '.$int);
		}

		/**********************************************************************
		*  Another oracle specific function - if you have set up a sequence
		*  this function returns the next ID from that sequence
		*/

		function insert_id($seq_name)
		{
			global $ezsql_oracle8_9_str;

			$return_val = $this->get_var("SELECT $seq_name.nextVal id FROM Dual");

			// If no return value then try to create the sequence
			if ( ! $return_val )
			{
				$this->query("CREATE SEQUENCE $seq_name maxValue 9999999999 INCREMENT BY 1 START WITH 1 CACHE 20 CYCLE");
				$return_val = $this->get_var("SELECT $seq_name.nextVal id FROM Dual");
				$this->register_error($ezsql_oracle8_9_str[2].": $seq_name");
				$this->show_errors ? trigger_error($ezsql_oracle8_9_str[2].": $seq_name",E_USER_NOTICE) : null;
			}

			return $return_val;
		}

		/**********************************************************************
		*  Perform Oracle query and try to determine result value
		*/

		function query($query)
		{

			$return_value = 0;

			// Flush cached values..
			$this->flush();

			// Log how the function was called
			$this->func_call = "\$db->query(\"$query\")";

			// Keep track of the last query for debug..
			$this->last_query = $query;

			$this->count(true, true);

			// Use core file cache function
			if ( $cache = $this->get_cache($query) )
			{
				return $cache;
			}

			// If there is no existing database connection then try to connect
			if ( ! isset($this->dbh) || ! $this->dbh )
			{
				$this->connect($this->dbuser, $this->dbpassword, $this->dbname);
			}

			// Parses the query and returns a statement..
			if ( ! $stmt = oci_parse($this->dbh, $query))
			{
				$error = oci_error($this->dbh);
				$this->register_error($error["message"]);
				$this->show_errors ? trigger_error($error["message"],E_USER_WARNING) : null;
				return false;
			}

			// Execut the query..
			elseif ( ! $this->result = oci_execute($stmt))
			{
				$error = oci_error($stmt);
				$this->register_error($error["message"]);
				$this->show_errors ? trigger_error($error["message"],E_USER_WARNING) : null;
				return false;
			}

			// If query was an insert
			$is_insert = false;
			if ( preg_match('/^(insert|delete|update|create) /i', $query) )
			{
				$is_insert = true;

				// num afected rows
				$return_value = $this->rows_affected = @oci_num_rows($stmt);
			}
			// If query was a select
			else
			{

				// Get column information
				if ( $num_cols = @OCINumCols($stmt) )
				{
					// Fetch the column meta data
	    			for ( $i = 1; $i <= $num_cols; $i++ )
	    			{
                                    
                                    if ( !is_object($this->col_info) ) {
                                        $this->col_info[] = new stdClass;
                                    }
                                    
	    				$this->col_info[($i-1)]->name = @OCIColumnName($stmt,$i);
	    				$this->col_info[($i-1)]->type = @OCIColumnType($stmt,$i);
	    				$this->col_info[($i-1)]->size = @OCIColumnSize($stmt,$i);
				    }
				}
                            
                            // Store Query Results 
                            $num_rows=0; 
                            while ( $row = @oci_fetch_object($stmt) ) 
                            { 
                                // Store relults as an objects within main array 
                                $this->last_result[$num_rows] = $row; 
                                $num_rows++; 
                            } 

                            // num result rows
                            $return_value = $num_rows;
			}

			// disk caching of queries
			$this->store_cache($query,$is_insert);

			// If debug ALL queries
			$this->trace || $this->debug_all ? $this->debug() : null ;

			return $return_value;

		}

	}
