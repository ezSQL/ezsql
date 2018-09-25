<?php

	/**********************************************************************
          * ezSQL Database specific class - PostgreSQL
          * Desc..: PostgreSQL component (part of ezSQL databse abstraction library)
          *
          * @author  Justin Vincent (jv@jvmultimedia.com)
          * @author  Stefanie Janine Stoelting <mail@stefanie-stoelting.de>
          * Contributor:  Lawrence Stubbs <technoexpressnet@gmail.com>
          * @link	   http://twitter.com/justinvincent
          * @name	   ezSQL_postgresql
          * @package ezSQL
          * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
          *
          */
	class ezSQL_postgresql extends ezSQLcore
	{
		
        /**********************************************************************
                     *  ezSQL error strings - PostgreSQL
                     */
		private $_ezsql_postgresql_str = array
			(
				1 => 'Require $dbuser and $dbpassword to connect to a database server',
				2 => 'Error establishing PostgreSQL database connection. Correct user/password? Correct hostname? Database server running?',
				3 => 'Require $dbname to select a database',
				4 => 'mySQL database connection is not active',
				5 => 'Unexpected error while trying to select database'
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
		* Database name
		* @var string
		*/
		private $_dbname;

		/**
		* Host name or IP address
		* @var string
		*/
		private $_dbhost;

		/**
		* TCP/IP port of PostgreSQL
		* @var string Default is PostgreSQL default port 5432
		*/
		private $_dbport = '5432';

		/**
		* Show errors
		* @var boolean Default is true
		*/
		public $show_errors = true;
						
		/**
		* Database connection
		* @var resource
		*/
		public $dbh;
		private $result;
        
		private $rows_affected = false;
        
		protected $preparedvalues = array();

		/**
		* Constructor - allow the user to perform a qucik connect at the same time
		* as initialising the ezSQL_postgresql class
		*
		* @param string $dbuser The database user name
		* @param string $dbpassword The database users password
		* @param string $dbname The name of the database
		* @param string $dbhost The host name or IP address of the database server.
		*			Default is localhost
		* @param string $dbport The database TCP/IP port
		*			Default is PostgreSQL default port 5432
		*/
		public function __construct($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $dbport='5432') {
			if ( ! function_exists ('pg_connect') ) {
				throw new Exception('<b>Fatal Error:</b> ezSQL_postgresql requires PostgreSQL Lib to be compiled and or linked in to the PHP engine');
			}
			if ( ! class_exists ('ezSQLcore') ) {
				throw new Exception('<b>Fatal Error:</b> ezSQL_postgresql requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');
			}

			parent::__construct();

			$this->_dbuser = $dbuser;
			$this->_dbpassword = $dbpassword;
			$this->_dbname = $dbname;
			$this->_dbhost = $dbhost;
			$this->_dbport = $dbport;
            
            global $_ezPostgresql;
            $_ezPostgresql = $this;
		} // __construct

		/**
		* In the case of PostgreSQL quick_connect is not really needed because std.
		* connect already does what quick connect does - but for the sake of
		* consistency it has been included
		*
		* @param string $dbuser The database user name
		* @param string $dbpassword The database users password
		* @param string $dbname The name of the database
		* @param string $dbhost The host name or IP address of the database server.
		*			Default is localhost
		* @param string $dbport The database TCP/IP port
		*		  Default is PostgreSQL default port 5432
		* @return boolean
		*/
		function quick_connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $dbport='5432') {
			if ( ! $this->connect($dbuser, $dbpassword, $dbname, $dbhost, $dbport, true) ) ;						
			return $this->_connected;
		} // quick_connect

		/**********************************************************************
		* Try to connect to PostgreSQL database server
		*
		* @param string $dbuser The database user name
		* @param string $dbpassword The database users password
		* @param string $dbname The name of the database
		* @param string $dbhost The host name or IP address of the database server.
		*			Default is localhost
		* @param string $dbport The database TCP/IP port
		*						Default is PostgreSQL default port 5432
		* @return boolean
		*/
		public function connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $dbport='5432') {
			$this->_connected = false;
						
			$this->_dbuser = empty($dbuser) ? $this->_dbuser : $dbuser;
			$this->_dbpassword = empty($dbpassword) ? $this->_dbpassword : $dbpassword;
			$this->_dbname = empty($dbname) ? $this->_dbname : $dbname;
			$this->_dbhost = $dbhost!='localhost' ? $this->_dbhost : $dbhost;
			$this->_dbport = $dbport!='5432' ? $dbport : $this->_dbport;

			if ( !$this->_dbuser ) {
				// Must have a user and a password
				$this->register_error($this->_ezsql_postgresql_str[1] . ' in ' . __FILE__ . ' on line ' . __LINE__);
				$this->show_errors ? trigger_error($this->_ezsql_postgresql_str[1], E_USER_WARNING) : null;
			} else if ( ! $this->dbh = pg_connect("host=$this->_dbhost port=$this->_dbport dbname=$this->_dbname user=$this->_dbuser password=$this->_dbpassword", true) ) {
				// Try to establish the server database handle
				$this->register_error($this->_ezsql_postgresql_str[2] . ' in ' . __FILE__ . ' on line ' . __LINE__);
				$this->show_errors ? trigger_error($this->_ezsql_postgresql_str[2], E_USER_WARNING) : null;
			} else {
				$this->_connected = true;
			}

			return $this->_connected;
		} // connect

		/**
		* Format a mySQL string correctly for safe mySQL insert
		* (no matter if magic quotes are on or not)
		*
		* @param string $str
		* @return string
		*/
		public function escape($str) {
			return pg_escape_string(stripslashes($str));
		} // escape

		/**
		* Return PostgreSQL specific system date syntax
		* i.e. Oracle: SYSDATE Mysql: NOW()
		*
		* @return string
		*/
		public function sysdate() {
			return 'NOW()';
		} // sysdate

		/**
		* Return PostgreSQL specific values: Return all tables of the current
		* schema
		*
		* @return string
		*/
		public function showTables() {
			return "SELECT table_name FROM information_schema.tables WHERE table_schema = '$this->_dbname' AND table_type='BASE TABLE'";
		} // showTables

		/**
		* Return the description of the given table
		*
		* @param string $tbl_name The table name
		* @return string
		*/
		public function descTable($tbl_name) {
			return "SELECT ordinal_position, column_name, data_type, column_default, is_nullable, character_maximum_length, numeric_precision FROM information_schema.columns WHERE table_name = '$tbl_name' AND table_schema='$this->_dbname' ORDER BY ordinal_position";
		} // descTable

		/**
		* Return all databases of the current server
		*
		* @return string
		*/
		public function showDatabases() {
			return "SELECT datname FROM pg_database WHERE datname NOT IN ('template0', 'template1') ORDER BY 1";
		} // showDatabases

		/**
		* Perform PostgreSQL query and try to determine result value
		*
		* @param string $query
		* @return boolean
		*/
		/**********************************************************************
		*  Perform PostgreSQL query and try to determine result value
		*/

		function query($query, $use_prepare=false)
		{
            if ($use_prepare)
                $param = &$this->getParamaters();
            
			// check for ezQuery placeholder tag and replace tags with proper prepare tag
			if (!empty($param) && is_array($param) && ($this->getPrepare()) && (strpos($query, _TAG) !== false))
			{
				foreach ($param as $i => $value) {
					$parametrize = $i + 1;
					$needle = _TAG;
					$pos = strpos($query, $needle);
					if ($pos !== false) 
						$query = substr_replace($query, '$'.$parametrize, $pos, strlen($needle));
				}
			}				

			// Initialize return
			$return_val = 0;

			// Flush cached values..
			$this->flush();

			// For reg expressions
			$query = trim($query);

			// Log how the function was called
			$this->log_query("\$db->query(\"$query\")");

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
				$this->connect($this->dbuser, $this->dbpassword, $this->dbname, $this->dbhost, $this->port);
			}
            
			// Perform the query via std postgresql_query function..
			if (!empty($param) && is_array($param) && ($this->getPrepare())){
				$this->result = @pg_query_params($this->dbh, $query, $param);		
				$this->setParamaters();				
			} else 
				$this->result = @pg_query($this->dbh, $query);


			// If there is an error then take note of it..
			if ( $str = @pg_last_error($this->dbh) )
			{
				$this->register_error($str);
				$this->show_errors ? trigger_error($str,E_USER_WARNING) : null;
				return false;
			}
			// Query was an insert, delete, update, replace
			$is_insert = false;
			if ( preg_match("/^(insert|delete|update|replace)\s+/i",$query) )
			{
				$is_insert = true;
				$this->rows_affected = @pg_affected_rows($this->result);

				// Take note of the insert_id
				if ( preg_match("/^(insert|replace)\s+/i",$query) )
				{
					//$this->insert_id = @postgresql_insert_id($this->dbh);
					//$this->insert_id = pg_last_oid($this->result);

					// Thx. Rafael Bernal
					$insert_query = pg_query("SELECT lastval();");
					$insert_row = pg_fetch_row($insert_query);
					$this->insert_id = $insert_row[0];
				}

				// Return number for rows affected
 				$return_val = $this->rows_affected;
				
				if ( preg_match("/returning/smi",$query) )
				{
					while ( $row = @pg_fetch_object($this->result) )
					{
						$return_valx[] = $row;
					}
					$return_val = $return_valx;
				}
			}
			// Query was a select
			else
			{ 
				$num_rows=0;
				if ( $this->result )	//may be needed but my tests did not
				{	
							
				// =======================================================
				// Take note of column info

				$i=0;
				while ($i < @pg_num_fields($this->result))
					{
						$this->col_info[$i]->name = pg_field_name($this->result,$i);
						$this->col_info[$i]->type = pg_field_type($this->result,$i);
						$this->col_info[$i]->size = pg_field_size($this->result,$i);
						$i++;
					}

				// =======================================================
				// Store Query Results

				//while ( $row = @pg_fetch_object($this->result, $i, PGSQL_ASSOC) ) doesn't work? donno
				//while ( $row = @pg_fetch_object($this->result,$num_rows) ) does work
				while ( $row = @pg_fetch_object($this->result) )
					{
						// Store results as an objects within main array
						$this->last_result[$num_rows] = $row ;
						$num_rows++;
					}

				@pg_free_result($this->result);
				}
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
		* Close the database connection
		*/
		public function disconnect() {
			if ( $this->dbh ) {
				pg_close($this->dbh);
				$this->_connected = false;
			}
		} // disconnect

		/**
		* Returns the current database server host
		*
		* @return string
		*/
		public function getDBHost() {
			return $this->_dbhost;
		} // getDBHost

		/**
		* Returns the current TCP/IP port
		*
		* @return string
		*/
		public function getPort() {
			return $this->_dbport;
		} // getPort

	} // ezSQL_postgresql