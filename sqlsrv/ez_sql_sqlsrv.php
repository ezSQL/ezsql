<?php


	/**********************************************************************
	*  Author: davisjw (davisjw@gmail.com)
	*  Web...: http://twitter.com/justinvincent
	*  Name..: ezSQL_sqlsrv
	*  Desc..: Microsoft Sql Server component (MS drivers) (part of ezSQL databse abstraction library) - based on ezSql_msSql library class.
	*
	*/

	/**********************************************************************
	*  ezSQL error strings - sqlsrv
	*/

	global $ezsql_sqlsrv_str;
	
	$ezsql_sqlsrv_str = array
	(
		1 => 'Require $dbuser and $dbpassword to connect to a database server',
		2 => 'Error establishing sqlsrv database connection. Correct user/password? Correct hostname? Database server running?',
		3 => 'Require $dbname to select a database',
		4 => 'SQL Server database connection is not active',
		5 => 'Unexpected error while trying to select database'
	);
	
	/**********************************************************************
	*  ezSQL non duplicating data type id's; converting dtype ids to str
	*/
	
	$ezsql_sqlsrv_type2str_non_dup = array
	(
		-5 => 'bigint', -7 => 'bit', 1 => 'char', 91 => 'date', -155 => 'datetimeoffset', 6 => 'float', -4 => 'image', 4 => 'int', -8 => 'nchar',
		-10 => 'ntext', 2 => 'numeric', -9 => 'nvarchar', 7 => 'real', 5 => 'smallint', -1 => 'text', -154 => 'time', -6 => 'tinyint', -151 => 'udt', 
		-11 => 'uniqueidentifier', -3 => 'varbinary', 12 => 'varchar', -152 => 'xml'
	);



	/**********************************************************************
	*  ezSQL Database specific class - sqlsrv
	*/

	if ( ! function_exists ('sqlsrv_connect') ) die('<b>Fatal Error:</b> ezSQL_sqlsrv requires the Microsoft PHP SQL Drivers to be installed. Also enable MS-SQL extension in PHP.ini file ');
	if ( ! class_exists ('ezSQLcore') ) die('<b>Fatal Error:</b> ezSQL_sqlsrv requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');

	class ezSQL_sqlsrv extends ezSQLcore
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
		*  same time as initialising the ezSQL_mssql class
		*/

		function ezSQL_sqlsrv($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $convertMySqlToMSSqlQuery=true)
		{
			$this->dbuser = $dbuser;
			$this->dbpassword = $dbpassword;
			$this->dbname = $dbname;
			$this->dbhost = $dbhost;
			$this->convertMySqlToMSSqlQuery = $convertMySqlToMSSqlQuery;
		}

		/**********************************************************************
		*  Short hand way to connect to mssql database server
		*  and select a mssql database at the same time
		*/

		function quick_connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost')
		{
			$return_val = false;
			if ( ! $this->connect($dbuser, $dbpassword, $dbname, $dbhost) ) ;
			else $return_val = true;
			return $return_val;
		}

		/**********************************************************************
		*  Try to connect to mssql database server
		*/

		function connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost')
		{
			global $ezsql_sqlsrv_str; $return_val = false;

			// Blank dbuser assumes Windows Authentication
			$connectionOptions["Database"] =$dbname;
			if ( $dbuser ) {
				$connectionOptions["UID"] = $dbuser;
				$connectionOptions["PWD"] = $dbpassword;
			}
//			$connectionOptions = array("UID" => $dbuser, "PWD" => $dbpassword, "Database" => $dbname);

			if ( ( $this->dbh = @sqlsrv_connect($dbhost, $connectionOptions) ) === false )
			{
				$this->register_error($ezsql_sqlsrv_str[2].' in '.__FILE__.' on line '.__LINE__);
				$this->show_errors ? trigger_error($ezsql_sqlsrv_str[2],E_USER_WARNING) : null;
			}
			else
			{
				$this->dbuser = $dbuser;
				$this->dbpassword = $dbpassword;
				$this->dbhost = $dbhost;
				$return_val = true;
			}

			return $return_val;
		}


		/**********************************************************************
		*  Format a mssql string correctly for safe mssql insert
		*  (no mater if magic quotes are on or not)
		*/

		function escape($str)
		{
			//not sure about this.
			//applying following logic
			//1. add 1 more ' to ' character

			return  str_ireplace("'", "''", $str);

		}


		/**********************************************************************
		*  Return mssql specific system date syntax
		*  i.e. Oracle: SYSDATE mssql: NOW(), MS-SQL : getDate()
		*
		*  The SQLSRV drivers pull back the data into a Date class.  Converted
		*   it to a string inside of SQL in order to prevent this from ocurring
		*  ** make sure to use " AS <label>" after calling this...
		*/

		function sysdate()
		{
			return "convert(varchar, GetDate(), 9)";
		}

		/**********************************************************************
		*  Perform mssql query and try to detirmin result value
		*/

		function query($query)
		{

			//if flag to convert query from MySql syntax to MS-Sql syntax is true
			//convert the query
			if($this->convertMySqlToMSSqlQuery == true)
				$query = $this->ConvertMySqlToMSSql($query);


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
			if ( $cache = $this->get_cache($query) )
			{
				return $cache;
			}


			// If there is no existing database connection then try to connect
			if ( ! isset($this->dbh) || ! $this->dbh )
			{
				$this->connect($this->dbuser, $this->dbpassword, $this->dbname, $this->dbhost);
			}

			// Perform the query via std mssql_query function..

			$this->result = @sqlsrv_query($this->dbh, $query);

			// If there is an error then take note of it..
			if ($this->result === false )
			{
				$errors = sqlsrv_errors();
				if (!empty($errors)) {
					foreach ($errors as $error) {
						$sqlError = "ErrorCode: ".$error['code']." ### State: ".$error['SQLSTATE']." ### Error Message: ".$error['message']." ### Query: ".$query;
						$this->register_error($sqlError);
						$this->show_errors ? trigger_error($sqlError ,E_USER_WARNING) : null;
					}
				}

				return false;
			}

			// Query was an insert, delete, update, replace
			$is_insert = false;
			if ( preg_match("/^(insert|delete|update|replace)\s+/i",$query) )
			{
				$is_insert = true;
				$this->rows_affected = @sqlsrv_rows_affected($this->result);

				// Take note of the insert_id
				if ( preg_match("/^(insert|replace)\s+/i",$query) )
				{

					$identityresultset = @sqlsrv_query($this->dbh, "select SCOPE_IDENTITY()");

					if ($identityresultset != false )
					{
						$identityrow = @sqlsrv_fetch($identityresultset);
						$this->insert_id = $identityrow[0];
					}

				}

				// Return number of rows affected
				$return_val = $this->rows_affected;
			}
			// Query was a select
			else
			{

				// Take note of column info
				$i=0;
				foreach ( @sqlsrv_field_metadata( $this->result) as $field ) {
					foreach ($field as $name => $value) {
						$name = strtolower($name);
						if ($name == "size") $name = "max_length";
						else if ($name == "type") $name = "typeid";
						//DEFINED FOR E_STRICT
						$col = new StdClass();
						$col->{$name} = $value;
					}

					$col->type = $this->get_datatype($col);
					$this->col_info[$i++] = $col;
					unset($col);
				}

				// Store Query Results
				$num_rows=0;

				while ( $row = @sqlsrv_fetch_object($this->result) )
				{

					// Store relults as an objects within main array
					$this->last_result[$num_rows] = $row;
					$num_rows++;
				}

				@sqlsrv_free_stmt($this->result);

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
			global $ezsql_sqlsrv_type2str_non_dup;
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
						$datatype = $ezsql_sqlsrv_type2str_non_dup[$col->typeid];
						break;
				}
			}
			
			return $datatype;
		}


		/**********************************************************************
		*  Close the active SQLSRV connection
		*/

		function disconnect()
		{
			@sqlsrv_close($this->dbh);
		}


	} // end class
