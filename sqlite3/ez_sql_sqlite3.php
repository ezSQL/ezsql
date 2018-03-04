<?php

	/**********************************************************************
	*  Author: Justin Vincent (jv@jvmultimedia.com) / Silvio Wanka 
	*  Web...: http://twitter.com/justinvincent
	*  Name..: ezSQL_sqlite3
	*  Desc..: SQLite3 component (part of ezSQL databse abstraction library)
	*
	*/

	/**********************************************************************
	*  ezSQL error strings - SQLite
	*/

	global $ezsql_sqlite3_str;
	
	$ezsql_sqlite3_str = array
	(
		1 => 'Require $dbpath and $dbname to open an SQLite database'
	);

	/**********************************************************************
	*  ezSQL Database specific class - SQLite
	*/

	if ( ! class_exists ('SQLite3') ) die('<b>Fatal Error:</b> ezSQL_sqlite3 requires SQLite3 Lib to be compiled and or linked in to the PHP engine');
	if ( ! class_exists ('ezSQLcore') ) die('<b>Fatal Error:</b> ezSQL_sqlite3 requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');

	class ezSQL_sqlite3 extends ezSQLcore
	{

		var $rows_affected = false;

		/**********************************************************************
		*  Constructor - allow the user to perform a quick connect at the 
		*  same time as initialising the ezSQL_sqlite3 class
		*/

		function __construct($dbpath='', $dbname='')
		{
			// Turn on track errors 
			ini_set('track_errors',1);
			
			if ( $dbpath && $dbname )
			{
				$this->connect($dbpath, $dbname);
			}
		}

		/**********************************************************************
		*  Try to connect to SQLite database server
		*/

		function connect($dbpath='', $dbname='')
		{
			global $ezsql_sqlite3_str; $return_val = false;
			
			// Must have a user and a password
			if ( ! $dbpath || ! $dbname )
			{
				$this->register_error($ezsql_sqlite3_str[1].' in '.__FILE__.' on line '.__LINE__);
				$this->show_errors ? trigger_error($ezsql_sqlite3_str[1],E_USER_WARNING) : null;
			}
			// Try to establish the server database handle
			else if ( ! $this->dbh = @new SQLite3($dbpath.$dbname) )
			{
				$this->register_error($php_errormsg);
				$this->show_errors ? trigger_error($php_errormsg,E_USER_WARNING) : null;
			}
			else
			{
				$return_val = true;
				$this->conn_queries = 0;
			}

			return $return_val;			
		}

		/**********************************************************************
		*  In the case of SQLite quick_connect is not really needed
		*  because std. connect already does what quick connect does - 
		*  but for the sake of consistency it has been included
		*/

		function quick_connect($dbpath='', $dbname='')
		{
			return $this->connect($dbpath, $dbname);
		}

		/**********************************************************************
		*  No real equivalent of mySQL select in SQLite 
		*  once again, function included for the sake of consistency
		*/

		function select($dbpath='', $dbname='')
		{
			return $this->connect($dbpath, $dbname);
		}

		/**********************************************************************
		*  Format a SQLite string correctly for safe SQLite insert
		*  (no mater if magic quotes are on or not)
		*/

		function escape($str)
		{
			return $this->dbh->escapeString(stripslashes(preg_replace("/[\r\n]/",'',$str)));				
		}

		/**********************************************************************
		*  Return SQLite specific system date syntax 
		*  i.e. Oracle: SYSDATE Mysql: NOW()
		*/

		function sysdate()
		{
			return 'now';			
		}

		/**********************************************************************
		*  Perform SQLite query and try to detirmin result value
		*/

		// ==================================================================
		//	Basic Query	- see docs for more detail
	
		function query($query)
		{

			// For reg expressions
			$query = str_replace("/[\n\r]/",'',trim($query)); 

			// initialise return
			$return_val = 0;

			// Flush cached values..
			$this->flush();

			// Log how the function was called
			$this->func_call = "\$db->query(\"$query\")";

			// Keep track of the last query for debug..
			$this->last_query = $query;

			// Perform the query via std mysql_query function..
			$this->result = $this->dbh->query($query);
			$this->count(true, true);

			// If there is an error then take note of it..
			if (@$this->dbh->lastErrorCode())
			{
				$err_str = $this->dbh->lastErrorMsg();
				$this->register_error($err_str);
				$this->show_errors ? trigger_error($err_str,E_USER_WARNING) : null;
				return false;
			}
			
			// Query was an insert, delete, update, replace
			if ( preg_match("/^(insert|delete|update|replace)\s+/i",$query) )
			{
				$this->rows_affected = @$this->dbh->changes();
				
				// Take note of the insert_id
				if ( preg_match("/^(insert|replace)\s+/i",$query) )
				{
					$this->insert_id = @$this->dbh->lastInsertRowID();	
				}
				
				// Return number fo rows affected
				$return_val = $this->rows_affected;
	
			}
			// Query was an select
			else
			{
				
				// Take note of column info	
				$i=0;
				$this->col_info = array();
				while ($i < @$this->result->numColumns())
				{
					$this->col_info[$i] = new StdClass;
					$this->col_info[$i]->name       = $this->result->columnName($i);
					$this->col_info[$i]->type       = null;
					$this->col_info[$i]->max_length = null;
					$i++;
				}
				
				// Store Query Results
				$num_rows=0;
				while ($row =  @$this->result->fetchArray(SQLITE3_ASSOC))
				{
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
		
		}

	}

