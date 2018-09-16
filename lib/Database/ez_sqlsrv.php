<?php


	/**********************************************************************
	*  Author: davisjw (davisjw@gmail.com)
	*  Contributor:  Lawrence Stubbs <technoexpressnet@gmail.com>
	*  Web...: http://twitter.com/justinvincent
	*  Name..: ez_sqlsrv
	*  Desc..: Microsoft Sql Server component (MS drivers) (part of ezSQL databse abstraction library) - based on ezSql_msSql library class.
	*
	*/
	namespace ezsql\Database\ez_sqlsrv;
	use ezsql\Configuration;
	use ezsql\ezsqlModel;

	class ez_sqlsrv extends ezsqlModel
	{

		/**********************************************************************
		*  ezSQL error strings - sqlsrv
		*/
		private $ezsql_sqlsrv_str = array
		(
			1 => 'Require $dbuser and $dbpassword to connect to a database server',
			2 => 'Error establishing sqlsrv database connection. Correct user/password? Correct hostname? Database server running?',
			3 => 'Require $dbname to select a database',
			4 => 'SQL Server database connection is not active',
			5 => 'Unexpected error while trying to select database'
		);
		
		/**********************************************************************
		*  ezSQL non duplicating data type id's; converting type ids to str
		*/		
		private $ezsql_sqlsrv_type2str_non_dup = array
		(
			-5 => 'bigint', -7 => 'bit', 1 => 'char', 91 => 'date', -155 => 'datetimeoffset', 6 => 'float', -4 => 'image', 4 => 'int', -8 => 'nchar',
			-10 => 'ntext', 2 => 'numeric', -9 => 'nvarchar', 7 => 'real', 5 => 'smallint', -1 => 'text', -154 => 'time', -6 => 'tinyint', -151 => 'udt', 
			-11 => 'uniqueidentifier', -3 => 'varbinary', 12 => 'varchar', -152 => 'xml'
		);

		private $rows_affected = false;
        
		protected $preparedvalues = array();

		/**
		 * Database configuration setting 
		 * @var Configuration instance
		 */
		private $database;

		public function __construct(Configuration $settings) {
			if (empty($settings) || (!$settings instanceof Configuration)) {
				throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
			}
            parent::__construct();
			$this->database = $settings;
            
            $GLOBALS['_ezSqlsrv'] = $this;        
		}

		/**********************************************************************
		*  Short hand way to connect to sqlsrv database server
		*  and select a sqlsrv database at the same time
		*/
		public function quick_connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost')
		{
			$return_val = false;
            $this->_connected = false;
			if ( ! $this->connect($dbuser, $dbpassword, $dbname, $dbhost) ) ;
			else { $return_val = true;
                $this->_connected = true; }
			return $return_val;
		}

		/**********************************************************************
		*  Try to connect to sqlsrv database server
		*/
		public function connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost')
		{
			global $ezsql_sqlsrv_str; $return_val = false;
            $this->_connected = false;

			// Blank dbuser assumes Windows Authentication
			$connectionOptions["Database"] =$dbname;
			if ( $dbuser ) {
				$connectionOptions["UID"] = $dbuser;
				$connectionOptions["PWD"] = $dbpassword;
			}
			$connectionOptions = array("UID" => $dbuser, "PWD" => $dbpassword, "Database" => $dbname, "ReturnDatesAsStrings" => true);

			if ( ( $this->dbh = @sqlsrv_connect($dbhost, $connectionOptions) ) === false )
			{
				$this->register_error($ezsql_sqlsrv_str[2].' in '.__FILE__.' on line '.__LINE__);
				$this->show_errors ? trigger_error($ezsql_sqlsrv_str[2],E_USER_WARNING) : null;
                return false;
			} else {
				$this->dbuser = $dbuser;
				$this->dbpassword = $dbpassword;
				$this->dbhost = $dbhost;
				$return_val = true;
                $this->_connected = true;

				$this->conn_queries = 0;
			}

			return $return_val;
		}                

		/**********************************************************************
		*  Return sqlsrv specific system date syntax
		*  i.e. Oracle: SYSDATE sqlsrv: NOW(), MS-SQL : getDate()
		*
		*  The SQLSRV drivers pull back the data into a Date class.  Converted
		*   it to a string inside of SQL in order to prevent this from ocurring
		*  ** make sure to use " AS <label>" after calling this...
		*/
		public function sysdate()
		{
			return "GETDATE()";
		}

		/**********************************************************************
		*  Perform sqlsrv query and try to determine result value
		*/
		public function query($query, $use_prepare=false)
		{
            if ($use_prepare) 
                $param = &$this->getParamaters();
            
			// check for ezQuery placeholder tag and replace tags with proper prepare tag
			$query = str_replace(_TAG, '?', $query);

			//if flag to convert query from MySql syntax to MS-Sql syntax is true
			//convert the query
			if($this->convert_mySql == true)
				$query = $this->mySqlto($query);

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
				$this->connect($this->dbuser, $this->dbpassword, $this->dbname, $this->dbhost);
			}

			// Perform the query via std sqlsrv_query function..
			if (!empty($param) && is_array($param) && ($this->getPrepare())) {
				$this->result = @sqlsrv_query($this->dbh, $query, $param);
                $this->setParamaters();                
            }
			else 
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

					// Store results as an objects within main array
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
		   4. LIMIT keyword is replaced with TOP keyword. Warning: Logic not fully tested.

		   Note: This method is only a small attempt to convert the syntax. There are many aspects which are not covered here.
		   		This method doesn't at all guarantee complete conversion. Certain queries will still
		   		not work. e.g. MS SQL requires all columns in Select Clause to be present in 'group by' clause.
		   		There is no such restriction in MySql.
		*/
		public function mySqlto($query)
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

		public function get_datatype($col)
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
		public function disconnect()
		{
			$this->conn_queries = 0;
			@sqlsrv_close($this->dbh);
			$this->_connected = false;
		}


	} // end class
