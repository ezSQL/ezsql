<?php


	/**********************************************************************
	*  Author: davisjw (davisjw@gmail.com)
	*  Author: Lawrence Stubbs (technoexpressnet@gmail.com)
	*  Web...: http://twitter.com/justinvincent
	*  Name..: ezSQL_odbc
	*  Desc..: Microsoft Sql Server component (MS drivers) (part of ezSQL databse abstraction library) - based on ezSql_msSql library class.
	*
	*/

	/**********************************************************************
	*  ezSQL error strings - odbc
	*/

	global $ezsql_odbc_str;
	
	$ezsql_odbc_str = array
	(
		1 => 'Require $dbuser and $dbpassword to connect to a database server',
		2 => 'Error establishing odbc database connection. Correct user/password? Correct hostname? Database server running?',
		3 => 'Require $dbname to select a database',
		4 => 'SQL Server database connection is not active',
		5 => 'Unexpected error while trying to select database'
	);
	
	/**********************************************************************
	*  ezSQL non duplicating data type id's; converting dtype ids to str
	*/
	
	$ezsql_odbc_type2str_non_dup = array
	(
		-5 => 'bigint', -7 => 'bit', 1 => 'char', 91 => 'date', -155 => 'datetimeoffset', 6 => 'float', -4 => 'image', 4 => 'int', -8 => 'nchar',
		-10 => 'ntext', 2 => 'numeric', -9 => 'nvarchar', 7 => 'real', 5 => 'smallint', -1 => 'text', -154 => 'time', -6 => 'tinyint', -151 => 'udt', 
		-11 => 'uniqueidentifier', -3 => 'varbinary', 12 => 'varchar', -152 => 'xml'
	);



	/**********************************************************************
	*  ezSQL Database specific class - odbc
	*/

	if ( ! function_exists ('odbc_connect') ) die('<b>Fatal Error:</b> ezSQL_odbc requires the Microsoft Drivers for PHP for SQL Server to be installed. Also enable ODBC extension in PHP.ini file ');
	if ( ! class_exists ('ezSQLcore') ) die('<b>Fatal Error:</b> ezSQL_odbc requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');

	class ezSQL_odbc extends ezSQLcore
	{

		var $dbuser = false;
		var $dbpassword = false;
		var $dbname = false;
		var $dbhost = false;
		var $rows_affected = false;

		//if we want to convert Queries in MySql syntax to MS-SQL syntax. Yes, there
		//are some differences in query syntax.
		var $convertMySqlToMSSqlQuery = TRUE;

		/**********************************************************************
		*  Constructor - allow the user to perform a quick connect at the
		*  same time as initializing the ezSQL_mssql class
		*/

		function __construct($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $convertMySqlToMSSqlQuery=true)
		{
			$this->dbuser = $dbuser;
			$this->dbpassword = $dbpassword;
			$this->dbname = $dbname;
			$this->dbhost = $dbhost;
			$this->convertMySqlToMSSqlQuery = $convertMySqlToMSSqlQuery;
            
            global $_ezOdbc;
            $_ezOdbc = $this;
		}

		/**********************************************************************
		*  Short hand way to connect to odbc database server
		*  and select a odbc database at the same time
		*/

		function quick_connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost')
		{
			$return_val = false;
            $this->_connected = false;
			if ( ! $this->connect($dbuser, $dbpassword, $dbname, $dbhost) ) ;
			else { $return_val = true;
                $this->_connected = true; }
			return $return_val;
		}

		/**********************************************************************
		*  Try to connect to odbc database server
		*/

		function connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost')
		{
			global $ezsql_odbc_str; $return_val = false;
            $this->_connected = false;

			// Blank dbuser assumes Windows Authentication
			$connectionOptions["Database"] =$dbname;
			if ( $dbuser ) {
				$connectionOptions["UID"] = $dbuser;
				$connectionOptions["PWD"] = $dbpassword;
			}
			$connectionOptions = array("UID" => $dbuser, "PWD" => $dbpassword, "Database" => $dbname, "ReturnDatesAsStrings" => true);

			if ( ( $this->dbh = @odbc_connect($dbhost, $connectionOptions) ) === false )
			{
				$this->register_error($ezsql_odbc_str[2].' in '.__FILE__.' on line '.__LINE__);
				$this->show_errors ? trigger_error($ezsql_odbc_str[2],E_USER_WARNING) : null;
				return false;
			}
			else
			{
				$this->dbuser = $dbuser;
				$this->dbpassword = $dbpassword;
				$this->dbhost = $dbhost;
				$return_val = true;
                $this->_connected = true;

				$this->conn_queries = 0;
			}

			return $return_val;
		}
                
        function odbc_escape_string($data) {
            if ( !isset($data) ) return '';
            if ( is_numeric($data) ) return $data;

            $non_displayables = array(
                '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
                '/%1[0-9a-f]/',             // url encoded 16-31
                '/[\x00-\x08]/',            // 00-08
                '/\x0b/',                   // 11
                '/\x0c/',                   // 12
                '/[\x0e-\x1f]/'             // 14-31
                );
                
            foreach ( $non_displayables as $regex )
                $data = preg_replace( $regex, '', $data );
            $data = str_replace("'", "''", $data );
            return $data;
        }

		/**********************************************************************
		*  Format a odbc string correctly for safe odbc insert
		*  (no mater if magic quotes are on or not)
		*/

		function escape($str)
		{
			return  $this->odbc_escape_string($str);
		}
        
		/**********************************************************************
		*  Return odbc specific system date syntax
		*  i.e. Oracle: SYSDATE odbc: NOW(), MS-SQL : getDate()
		*
		*  The odbc drivers pull back the data into a Date class.  Converted
		*   it to a string inside of SQL in order to prevent this from ocurring
		*  ** make sure to use " AS <label>" after calling this...
		*/

		function sysdate()
		{
			return 'GETDATE()';
		}

		/**********************************************************************
		*  Perform odbc query and try to detirmin result value
		*/

		function query($query)
		{
			//if flag to convert query from MySql syntax to MS-Sql syntax is true
			//convert the query
			if($this->convertMySqlToMSSqlQuery == true)
				$query = $this->ConvertMySqlToMSSql($query);
			// Initialize return
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
			$this->count(true, true);
			// Use core file cache function
			if ( $cache = $this->get_cache($query) )
			{
				return $cache;
			}
			// If there is no existing database connection then try to connect
			if ( ! isset($this->dbh) || ! $this->dbh )
			{
				$this->connect($this->dbuser, $this->dbpassword, $this->dbhost);
			}
			// Perform the query via std odbc_query function..
			$this->result = @odbc_exec($query, $this->dbh);
			// If there is an error then take note of it..
			if ($this->result == false )
			{
				$get_errorcodeSql = "SELECT @@ERROR as errorcode";
				$error_res = @odbc_exec($get_errorcodeSql, $this->dbh);
				$errorCode = @odbc_result($error_res, 0, "errorcode");
				$get_errorMessageSql = "SELECT severity as errorSeverity, text as errorText FROM sys.messages  WHERE message_id = ".$errorCode  ;
				$errormessage_res =  @odbc_exec($get_errorMessageSql, $this->dbh);
				if($errormessage_res)
				{
					$errorMessage_Row = @odbc_fetch_row($errormessage_res);
					$errorSeverity = $errorMessage_Row[0];
					$errorMessage = $errorMessage_Row[1];
				}
				$sqlError = "ErrorCode: ".$errorCode." ### Error Severity: ".$errorSeverity." ### Error Message: ".$errorMessage." ### Query: ".$query;
				$this->register_error($sqlError);
				$this->show_errors ? trigger_error($sqlError ,E_USER_WARNING) : null;
				return false;
			}
			// Query was an insert, delete, update, replace
			if ( preg_match("/^(insert|delete|update|replace)\s+/i",$query) )
			{
				$is_insert = true;
				$this->rows_affected = @odbc_rows_affected($this->dbh);
				// Take note of the insert_id
				if ( preg_match("/^(insert|replace)\s+/i",$query) )
				{
					$identityresultset = @odbc_query("select SCOPE_IDENTITY()");
					if ($identityresultset != false )
					{
						$identityrow = @odbc_fetch_row($identityresultset);
						$this->insert_id = $identityrow[0];
					}
				}
				// Return number of rows affected
				$return_val = $this->rows_affected;
			}
			// Query was a select
			else
			{
				$is_insert = false;
				// Take note of column info
				$i=0;
				while ($i < @odbc_num_fields($this->result))
				{
					$this->col_info[$i] = @odbc_fetch_field($this->result);
					$i++;
				}
				// Store Query Results
				$num_rows=0;
				while ( $row = @odbc_fetch_object($this->result) )
				{
					// Store relults as an objects within main array
					$this->last_result[$num_rows] = $row;
					$num_rows++;
				}
				@odbc_free_result($this->result);
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
		}

		/**********************************************************************
		*  Convert a Query From MySql Syntax to MS-Sql syntax
		   Following conversions are made:-
		   1. The '`' character used for MySql queries is not supported - the character is removed.
		   2. FROM_UNIXTIME method is not supported. The Function is removed.It is replaced with
		      getDate(). Warning: This logic may not be right.
		   3. unix_timestamp function is removed.
		   4. LIMIT keyowrd is replaced with TOP keyword. Warning: Logic not fully tested.

		   Note: This method is only a small attempt to convert the syntax. There are many aspects which are not covered here.
		   		This method doesn't at all guarantee complete conversion. Certain queries will still
		   		not work. e.g. MS SQL requires all columns in Select Clause to be present in 'group by' clause.
		   		There is no such restriction in MySql.
		*/

		function ConvertMySqlToMSSql($query)
		{

			//replace the '`' character used for MySql queries, but not
			//supported in MS-Sql

			$query = str_replace("`", "", $query);
			$limit_str = "/LIMIT[^\w]{1,}([0-9]{1,})([\,]{0,})([0-9]{0,})/i";
			$patterns = array(
					0 => "/FROM_UNIXTIME\(([^\/]{0,})\)/i", 	//replace From UnixTime command in MS-Sql, doesn't work
					1 => "/unix_timestamp\(([^\/]{0,})\)/i", 	//replace unix_timestamp function. Doesn't work in MS-Sql
					2 => $limit_str);													//replace LIMIT keyword. Works only on MySql not on MS-Sql with TOP keyword
			$replacements = array(
					0 => "getdate()", 
					1 => "\\1", 
					2 => "");
			preg_match($limit_str, $query, $regs);
			$query = preg_replace($patterns, $replacements, $query);
			
			if(isset($regs[2]))
				$query = str_ireplace("SELECT ", "SELECT TOP ".$regs[3]." ", $query);
			else if(isset($regs[1]))
				$query  = str_ireplace("SELECT ", "SELECT TOP ".$regs[1]." ", $query);

			return $query;

		}

		function get_datatype($col)
		{
			global $ezsql_odbc_type2str_non_dup;
			$datatype = "dt not defined";
			if(isset($col->typeid))
			{
				switch ($col->typeid) {
					case -2 :
						if ($col->max_length < 8000)
							$datatype = "binary";
						else
							$datatype = "timestamp";
						break;
					case 3 :
						if (($col->scale == 4) && ($col->precision == 19))
							$datatype = "money";
						else if (($col->scale == 4) && ($col->precision == 10))
							$datatype = "smallmoney";
						else
							$datatype = "decimal";
						break;
					case 93 :
						if (($col->precision == 16) && ($col->scale == 0))
							$datatype = "smalldatetime";
						else if (($col->precision == 23) && ($col->scale == 3))
							$datatype = "datetime";
						else
							$datatype = "datetime2";
						break;
					default :
						$datatype = $ezsql_odbc_type2str_non_dup[$col->typeid];
						break;
				}
			}
			
			return $datatype;
		}


		/**********************************************************************
		*  Close the active odbc connection
		*/

		function disconnect()
		{
			$this->conn_queries = 0;
			@odbc_close($this->dbh);
            $this->_connected = true;
		}


	} // end class
