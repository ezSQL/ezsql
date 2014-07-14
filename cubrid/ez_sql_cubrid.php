<?php

	/**********************************************************************
	*  Author: Justin Vincent (jv@jvmultimedia.com)
	*  Web...: http://twitter.com/justinvincent
	*  Name..: ezSQL_cubrid
	*  Desc..: CUBRID component (part of ezSQL databse abstraction library)
	*
	*/

	/**********************************************************************
	*  ezSQL error strings - CUBRID
	*/

	global $ezSQL_cubrid_str;
	
	$ezSQL_cubrid_str = array
	(
		1 => 'Require $dbuser and $dbname to connect to a database server',
		2 => 'Error establishing CUBRID database connection. Correct user/password? Correct hostname? Correct database name and port ? Database server running?'
	);

	/**********************************************************************
	*  ezSQL Database specific class - CUBRID
	*/

	if ( ! function_exists ('cubrid_connect') ) die('<b>Fatal Error:</b> ezSQL_cubrid requires CUBRID PHP Driver to be compiled and or linked in to the PHP engine');
	if ( ! class_exists ('ezSQLcore') ) die('<b>Fatal Error:</b> ezSQL_cubrid requires ezSQLcore (ez_sql_core.php) to be included/loaded before it can be used');

	class ezSQL_cubrid extends ezSQLcore
	{

		var $dbuser = false;
		var $dbpassword = false;
		var $dbname = false;
		var $dbhost = false;
        var $dbport = false;
		var $rows_affected = false;

		/**********************************************************************
		*  Constructor - allow the user to perform a quick connect at the
		*  same time as initialising the ezSQL_cubrid class
		*/

		function ezSQL_cubrid($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $dbport=33000)
		{
			$this->dbuser = $dbuser;
			$this->dbpassword = $dbpassword;
			$this->dbname = $dbname;
			$this->dbhost = $dbhost;
            $this->dbport = $dbport;
		}

		/**********************************************************************
		*  In the case of CUBRID quick_connect is not really needed
		*  because std. connect already does what quick connect does -
		*  but for the sake of consistency it has been included
		*/
		function quick_connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $dbport=33000)
		{
			return $this->connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $dbport=33000);
		}
        
		/**********************************************************************
		*  Try to connect to CUBRID database server
		*/

		function connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $dbport=33000)
		{
			global $ezSQL_cubrid_str; $return_val = false;
			
			// Keep track of how long the DB takes to connect
			$this->timer_start('db_connect_time');
            
			// Must have a user and a password
			if ( ! $dbuser || ! $dbname )
			{
				$this->register_error($ezSQL_cubrid_str[1].' in '.__FILE__.' on line '.__LINE__);
				$this->show_errors ? trigger_error($ezSQL_cubrid_str[1],E_USER_WARNING) : null;
			}
			// Try to establish the server database handle
			else if ( ! $this->dbh = @cubrid_connect($dbhost,$dbport,$dbname,$dbuser,$dbpassword) )
			{
				$this->register_error($ezSQL_cubrid_str[2].' in '.__FILE__.' on line '.__LINE__);
				$this->show_errors ? trigger_error($ezSQL_cubrid_str[2],E_USER_WARNING) : null;
			}
			else
			{
				$this->dbuser = $dbuser;
				$this->dbpassword = $dbpassword;
				$this->dbhost = $dbhost;
                $this->dbname = $dbname;
                $this->dbport = $dbport;
				$return_val = true;
			}

			return $return_val;
		}

		/**********************************************************************
		*  No real equivalent of mySQL select in CUBRID
		*  once again, function included for the sake of consistency
		*/

		function select($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $dbport=33000)
		{
			return $this->connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost', $dbport=33000);
		}

		/**********************************************************************
		*  Format a CUBRID string correctly for safe CUBRID insert
		*  (no mater if magic quotes are on or not)
		*/

		function escape($str)
		{
			// If there is no existing database connection then try to connect
			if ( ! isset($this->dbh) || ! $this->dbh )
			{
				$this->connect($this->dbuser, $this->dbpassword, $this->dbhost);
			}

			return cubrid_real_escape_string(stripslashes($str));
		}

		/**********************************************************************
		*  Return CUBRID specific system date syntax
		*  i.e. Oracle: SYSDATE Mysql/CUBRID: NOW()
		*/

		function sysdate()
		{
			return 'NOW()';
		}

		/**********************************************************************
		*  Perform CUBRID query and try to determine result value
		*/

		function query($query)
		{
			// This keeps the connection alive for very long running scripts
			if ( $this->num_queries >= 500 )
			{
				$this->disconnect();
				$this->connect($this->dbuser,$this->dbpassword,$this->dbname,$this->dbhost,$this->dbport);
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
			
			// Start timer
			$this->timer_start($this->num_queries);

			// Use core file cache function
			if ( $cache = $this->get_cache($query) )
			{
				// Keep tack of how long all queries have taken
				$this->timer_update_global($this->num_queries);

				// Trace all queries
				if ( $this->use_trace_log )
				{
					$this->trace_log[] = $this->debug(false);
				}
				
				return $cache;
			}

			// If there is no existing database connection then try to connect
			if ( ! isset($this->dbh) || ! $this->dbh )
			{
				$this->connect($this->dbuser, $this->dbpassword, $this->dbname, $this->dbhost, $this->dbport);
			}

			// Perform the query via std cubrid_query function..
			$this->result = @cubrid_query($query,$this->dbh);

			// If there is an error then take note of it..
			if ( $str = @cubrid_error($this->dbh) )
			{
				$this->register_error($str);
				$this->show_errors ? trigger_error($str,E_USER_WARNING) : null;
				return false;
			}

			// Query was an insert, delete, update, replace
			if ( preg_match("/^(insert|delete|update|replace|truncate|drop|create|alter)\s+/i",$query) )
			{
				$is_insert = true;
				$this->rows_affected = @cubrid_affected_rows($this->dbh);

				// Take note of the insert_id
				if ( preg_match("/^(insert|replace)\s+/i",$query) )
				{
					$this->insert_id = @cubrid_insert_id($this->dbh);
				}

				// Return number fo rows affected
				$return_val = $this->rows_affected;
			}
			// Query was a select
			else
			{
				$is_insert = false;

				// Take note of column info
				$i=0;
				while ($i < @cubrid_num_fields($this->result))
				{
					$this->col_info[$i] = @cubrid_fetch_field($this->result);
					$i++;
				}

				// Store Query Results
				$num_rows=0;
				while ( $row = @cubrid_fetch_object($this->result) )
				{
					// Store relults as an objects within main array
					$this->last_result[$num_rows] = $row;
					$num_rows++;
				}

				@cubrid_free_result($this->result);

				// Log number of rows the query returned
				$this->num_rows = $num_rows;

				// Return number of rows selected
				$return_val = $this->num_rows;
			}

			// disk caching of queries
			$this->store_cache($query,$is_insert);

			// If debug ALL queries
			$this->trace || $this->debug_all ? $this->debug() : null ;

			// Keep tack of how long all queries have taken
			$this->timer_update_global($this->num_queries);

			// Trace all queries
			if ( $this->use_trace_log )
			{
				$this->trace_log[] = $this->debug(false);
			}

			return $return_val;

		}
		
		/**********************************************************************
		*  Close the active CUBRID connection
		*/

		function disconnect()
		{
			@cubrid_close($this->dbh);	
		}

	}
