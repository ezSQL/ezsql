<?php
	/**********************************************************************
	*  Author: Justin Vincent (jv@vip.ie)
           * Author: Stefanie Janine Stoelting <mail@stefanie-stoelting.de>
           * Contributor:  Lawrence Stubbs <technoexpressnet@gmail.com>
	*  Web...: http://justinvincent.com
	*  Name..: ezSQL
	*  Desc..: ezSQL Core module - database abstraction library to make
	*          it very easy to deal with databases. ezSQLcore can not be used by
	*          itself (it is designed for use by database specific modules).
           *
	*/

	/**********************************************************************
	*  ezSQL Constants
	*/

	defined('EZSQL_VERSION') or define('EZSQL_VERSION', '3.08');
	defined('OBJECT') or define('OBJECT', 'OBJECT');
	defined('ARRAY_A') or define('ARRAY_A', 'ARRAY_A');
	defined('ARRAY_N') or define('ARRAY_N', 'ARRAY_N');

	/**********************************************************************
	*  Core class containing common functions to manipulate query result
	*  sets once returned
	*/

    require_once('ezFunctions.php');
    require_once('ezQuery.php');
	class ezSQLcore extends ezQuery
	{		
    
		public $trace            = false;  // same as $debug_all
		public $debug_all        = false;  // same as $trace
		public $debug_called     = false;
		public $vardump_called   = false;
		public $show_errors      = true;
		public $num_queries      = 0;
		public $conn_queries     = 0;
		public $last_query       = null;
		public $last_error       = null;
		public $col_info         = null;
		public $captured_errors  = array();
		public $cache_dir        = false;
		public $cache_queries    = false;
		public $cache_inserts    = false;
		public $use_disk_cache   = false;
		public $cache_timeout    = 24; // hours
		public $timers           = array();
		public $total_query_time = 0;
		public $db_connect_time  = 0;
		public $trace_log        = array();
		public $use_trace_log    = false;
		public $sql_log_file     = false;
		public $do_profile       = false;
		public $profile_times    = array();
		public $insert_id        = null;
		
    /**
     * Whether the database connection is established, or not
     * @public boolean Default is false
     */
    protected $_connected = false;    
    /**
     * Contains the number of affected rows of a query
     * @public int Default is 0
     */
    protected $_affectedRows = 0;

    /**
     * The last query result
     * @public object Default is null
     */
    public $last_result = null;

    /**
     * Get data from disk cache
     * @public boolean Default is false
     */
    public $from_disk_cache = false;

    /**
     * Function called
     * @private string
     */
    private $func_call; 

	/**
     * All functions called
     * @private array 
     */
    private $all_func_calls = array();

		// == TJH == default now needed for echo of debug function
		public $debug_echo_is_on = true;

		/**********************************************************************
		*  Constructor
		*/
		function __construct()
		{
            parent::__construct();
		}

		/**********************************************************************
		*  Get host and port from an "host:port" notation.
		*  Returns array of host and port. If port is omitted, returns $default
		*/
		function get_host_port( $host, $default = false )
		{
			$port = $default;
			if ( false !== strpos( $host, ':' ) ) {
				list( $host, $port ) = explode( ':', $host );
				$port = (int) $port;
			}
			return array( $host, $port );
		}

		/**********************************************************************
		*  Print SQL/DB error - over-ridden by specific DB class
		*/
		function register_error($err_str)
		{
			// Keep track of last error
			$this->last_error = $err_str;

			// Capture all errors to an error array no matter what happens
			$this->captured_errors[] = array
			(
				'error_str' => $err_str,
				'query'     => $this->last_query
			);
		}

		/**********************************************************************
		*  Turn error handling on or off..
		*/
		function show_errors()
		{
			$this->show_errors = true;
		}

		function hide_errors()
		{
			$this->show_errors = false;
		}

		/**********************************************************************
		*  Kill cached query results
		*/
		function flush()
		{
			// Get rid of these
			$this->last_result = null;
			$this->col_info = null;
			$this->last_query = null;
			$this->from_disk_cache = false;
            $this->setParamaters();
		}

		/**********************************************************************
		* Log how the query function was called
		* @param string
		*/
		function log_query($query)
		{
			// Log how the last function was called
			$this->func_call = $query;
			
			// Keep an running Log of all functions called
			array_push($this->all_func_calls, $this->func_call);
		}

		/**********************************************************************
		* Get one variable from the DB - see docs for more detail
		*/
		function get_var($query=null,$x=0,$y=0, $use_prepare=false)
		{
			// Log how the function was called
			$this->log_query("\$db->get_var(\"$query\",$x,$y)");

			// If there is a query then perform it if not then use cached results..
			if ( $query)
			{
				$this->query($query, $use_prepare);
			}

			// Extract public out of cached results based x,y vals
			if ( $this->last_result[$y] )
			{
				$values = array_values(get_object_vars($this->last_result[$y]));
			}
			
			// If there is a value return it else return null
			return (isset($values[$x]) && $values[$x]!=='')?$values[$x]:null;
		}

		/**********************************************************************
		*  Get one row from the DB - see docs for more detail
		*/
		function get_row($query=null,$output=OBJECT,$y=0, $use_prepare=false)
		{
			// Log how the function was called
			$this->log_query("\$db->get_row(\"$query\",$output,$y)");

			// If there is a query then perform it if not then use cached results..
			if ( $query )
			{
				$this->query($query, $use_prepare);
			}

			// If the output is an object then return object using the row offset..
			if ( $output == OBJECT )
			{
				return $this->last_result[$y]?$this->last_result[$y]:null;
			}
			// If the output is an associative array then return row as such..
			elseif ( $output == ARRAY_A )
			{
				return $this->last_result[$y]?get_object_vars($this->last_result[$y]):null;
			}
			// If the output is an numerical array then return row as such..
			elseif ( $output == ARRAY_N )
			{
				return $this->last_result[$y]?array_values(get_object_vars($this->last_result[$y])):null;
			}
			// If invalid output type was specified..
			else
			{
				$this->show_errors ? trigger_error(" \$db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N",E_USER_WARNING) : null;
			}
		}

		/**********************************************************************
		*  Function to get 1 column from the cached result set based in X index
		*  see docs for usage and info
		*/

		function get_col($query=null,$x=0, $use_prepare=false)
		{

			$new_array = array();

			// If there is a query then perform it if not then use cached results..
			if ( $query )
			{
				$this->query($query, $use_prepare);
			}

			// Extract the column values
			$j = count($this->last_result);
			for ( $i=0; $i < $j; $i++ )
			{
				$new_array[$i] = $this->get_var(null,$x,$i);
			}

			return $new_array;
		}

		/**********************************************************************
		*  Return the the query as a result set, will use prepare statements if setup - see docs for more details
		*/
		function get_results($query=null, $output = OBJECT, $use_prepare=false) {
			// Log how the function was called
			$this->log_query("\$db->get_results(\"$query\", $output, $use_prepare)");

			// If there is a query then perform it if not then use cached results..
			if ( $query ) {
				$this->query($query, $use_prepare);
			}

			if ( $output == OBJECT ) {
				return $this->last_result;
			} elseif ( $output == _JSON ) { 
				return json_encode($this->last_result); // return as json output
			} elseif ( $output == ARRAY_A || $output == ARRAY_N ) {
				if ( $this->last_result ) {
					$i=0;
					foreach( $this->last_result as $row ) {
						$new_array[$i] = get_object_vars($row);
						if ( $output == ARRAY_N ) {
							$new_array[$i] = array_values($new_array[$i]);
						}
						$i++;
					}
					return $new_array;
				} else {
					return array();
				}
			}
		}
					
		/**********************************************************************
		*  Function to get column meta data info pertaining to the last query
		* see docs for more info and usage
		*/
		function get_col_info($info_type="name",$col_offset=-1)
		{
			if ( $this->col_info )
			{
				if ( $col_offset == -1 )
				{
					$i=0;
					foreach($this->col_info as $col )
					{
						$new_array[$i] = $col->{$info_type};
						$i++;
					}
					return $new_array;
				}
				else
				{
					return $this->col_info[$col_offset]->{$info_type};
				}
			}
		}

		/**********************************************************************
		*  store_cache
		*/
		function store_cache($query,$is_insert)
		{
			// The would be cache file for this query
			$cache_file = $this->cache_dir.'/'.md5($query);

			// disk caching of queries
			if ( $this->use_disk_cache && ( $this->cache_queries && ! $is_insert ) || ( $this->cache_inserts && $is_insert ))
			{
				if ( ! is_dir($this->cache_dir) )
				{
					$this->register_error("Could not open cache dir: $this->cache_dir");
					$this->show_errors ? trigger_error("Could not open cache dir: $this->cache_dir",E_USER_WARNING) : null;
				}
				else
				{
					// Cache all result values
					$result_cache = array
					(
						'col_info' => $this->col_info,
						'last_result' => $this->last_result,
						'num_rows' => $this->num_rows,
						'return_value' => $this->num_rows,
					);
					file_put_contents($cache_file, serialize($result_cache));
					if( file_exists($cache_file . ".updating") )
						unlink($cache_file . ".updating");
				}
			}
		}

		/**********************************************************************
		*  get_cache
		*/
		function get_cache($query)
		{
			// The would be cache file for this query
			$cache_file = $this->cache_dir.'/'.md5($query);

			// Try to get previously cached version
			if ( $this->use_disk_cache && file_exists($cache_file) )
			{
				// Only use this cache file if less than 'cache_timeout' (hours)
				if ( (time() - filemtime($cache_file)) > ($this->cache_timeout*3600) &&
					!(file_exists($cache_file . ".updating") && (time() - filemtime($cache_file . ".updating") < 60)) )
				{
					touch($cache_file . ".updating"); // Show that we in the process of updating the cache
				}
				else
				{
					$result_cache = unserialize(file_get_contents($cache_file));

					$this->col_info = $result_cache['col_info'];
					$this->last_result = $result_cache['last_result'];
					$this->num_rows = $result_cache['num_rows'];

					$this->from_disk_cache = true;

					// If debug ALL queries
					$this->trace || $this->debug_all ? $this->debug() : null ;

					return $result_cache['return_value'];
				}
			}
		}

		/**********************************************************************
		*  Dumps the contents of any input variable to screen in a nicely
		*  formatted and easy to understand way - any type: Object, public or Array
		*/
		function vardump($mixed='')
		{
			// Start output buffering
			ob_start();

			echo "<p><table><tr><td bgcolor=ffffff><blockquote><font color=000090>";
			echo "<pre><font face=arial>";

			if ( ! $this->vardump_called )
			{
				echo "<font color=800080><b>ezSQL</b> (v".EZSQL_VERSION.") <b>Variable Dump..</b></font>\n\n";
			}

			$var_type = gettype ($mixed);
			print_r(($mixed?$mixed:"<font color=red>No Value / False</font>"));
			echo "\n\n<b>Type:</b> " . ucfirst($var_type) . "\n";
			echo "<b>Last Query</b> [$this->num_queries]<b>:</b> ".($this->last_query?$this->last_query:"NULL")."\n";
			echo "<b>Last Function Call:</b> " . ($this->func_call?$this->func_call:"None")."\n";
			
			if (count($this->all_func_calls)>1)
			{
				echo "<b>List of All Function Calls:</b><br>"; 
				foreach($this->all_func_calls as $func_string)
					echo "  " . $func_string ."<br>\n";
			}
			
			echo "<b>Last Rows Returned:</b> ".(count($this->last_result)>0 ? $this->last_result : '')."\n";
			echo "</font></pre></font></blockquote></td></tr></table>".$this->donation();
			echo "\n<hr size=1 noshade color=dddddd>";

			// Stop output buffering and capture debug HTML
			$html = ob_get_contents();
			ob_end_clean();

			// Only echo output if it is turned on
			if ( $this->debug_echo_is_on )
			{
				echo $html;
			}

			$this->vardump_called = true;			
			return $html;
		}

		/**********************************************************************
		*  Alias for the above function
		*/
		function dumpvar($mixed)
		{
			return $this->vardump($mixed);
		}

		/**********************************************************************
		*  Displays the last query string that was sent to the database & a
		* table listing results (if there were any).
		* (abstracted into a seperate file to save server overhead).
		*/
		function debug($print_to_screen=true)
		{
			// Start outup buffering
			ob_start();

			echo "<blockquote>";

			// Only show ezSQL credits once..
			if ( ! $this->debug_called )
			{
				echo "<font color=800080 face=arial size=2><b>ezSQL</b> (v".EZSQL_VERSION.") <b>Debug..</b></font><p>\n";
			}

			if ( $this->last_error )
			{
				echo "<font face=arial size=2 color=000099><b>Last Error --</b> [<font color=000000><b>$this->last_error</b></font>]<p>";
			}

			if ( $this->from_disk_cache )
			{
				echo "<font face=arial size=2 color=000099><b>Results retrieved from disk cache</b></font><p>";
			}

			echo "<font face=arial size=2 color=000099><b>Query</b> [$this->num_queries] <b>--</b> ";
			echo "[<font color=000000><b>$this->last_query</b></font>]</font><p>";
			echo "<font face=arial size=2 color=000099><b>Query Result..</b></font>";
			echo "<blockquote>";

			if ( $this->col_info )
			{
				// =====================================================
				// Results top rows
				echo "<table cellpadding=5 cellspacing=1 bgcolor=555555>";
				echo "<tr bgcolor=eeeeee><td nowrap valign=bottom><font color=555599 face=arial size=2><b>(row)</b></font></td>";

				for ( $i=0, $j=count($this->col_info); $i < $j; $i++ )
				{
					/* when selecting count(*) the maxlengh is not set, size is set instead. */
					echo "<td nowrap align=left valign=top><font size=1 color=555599 face=arial>{$this->col_info[$i]->type}";
					if (!isset($this->col_info[$i]->max_length))
					{
						echo "{$this->col_info[$i]->size}";
					} else {
						echo "{$this->col_info[$i]->max_length}";
					}
					echo "</font><br><span style='font-family: arial; font-size: 10pt; font-weight: bold;'>{$this->col_info[$i]->name}</span></td>";
				}
				echo "</tr>";

				// ======================================================
				// print main results
				if ( $this->last_result )
				{
					$i=0;
					foreach ( $this->get_results(null,ARRAY_N) as $one_row )
					{
						$i++;
						echo "<tr bgcolor=ffffff><td bgcolor=eeeeee nowrap align=middle><font size=2 color=555599 face=arial>$i</font></td>";

						foreach ( $one_row as $item )
						{
							echo "<td nowrap><font face=arial size=2>$item</font></td>";
						}
						echo "</tr>";
					}
				// if last result
				} else {
					echo "<tr bgcolor=ffffff><td colspan=".(count($this->col_info)+1)."><font face=arial size=2>No Results</font></td></tr>";
				}
				echo "</table>";
			// if col_info
			} else {
				echo "<font face=arial size=2>No Results</font>";
			}

			echo "</blockquote></blockquote>".$this->donation()."<hr noshade color=dddddd size=1>";

			// Stop output buffering and capture debug HTML
			$html = ob_get_contents();
			ob_end_clean();

			// Only echo output if it is turned on
			if ( $this->debug_echo_is_on && $print_to_screen)
			{
				echo $html;
			}

			$this->debug_called = true;
			return $html;
		}

		/**********************************************************************
		*  Naughty little function to ask for some remuniration!
		*/
		function donation()
		{
			return "<font size=1 face=arial color=000000>If ezSQL has helped <a href=\"https://www.paypal.com/xclick/business=justin%40justinvincent.com&item_name=ezSQL&no_note=1&tax=0\" style=\"color: 0000CC;\">make a donation!?</a> &nbsp;&nbsp;<!--[ go on! you know you want to! ]--></font>";
		}

		/**********************************************************************
		*  Timer related functions
		*/
		function timer_get_cur()
		{
			list($usec, $sec) = explode(" ",microtime());
			return ((float)$usec + (float)$sec);
		}

		function timer_start($timer_name)
		{
			$this->timers[$timer_name] = $this->timer_get_cur();
		}

		function timer_elapsed($timer_name)
		{
			return round($this->timer_get_cur() - $this->timers[$timer_name],2);
		}

		function timer_update_global($timer_name)
		{
			if ( $this->do_profile )
			{
				$this->profile_times[] = array
				(
					'query' => $this->last_query,
					'time' => $this->timer_elapsed($timer_name)
				);
			}
			$this->total_query_time += $this->timer_elapsed($timer_name);
		}

		/**********************************************************************
		* Creates a SET nvp sql string from an associative array (and escapes all values)
		*
		*  Usage:
		*
		*     $db_data = array('login'=>'jv','email'=>'jv@vip.ie', 'user_id' => 1, 'created' => 'NOW()');
		*
		*     $db->query("INSERT INTO users SET ".$db->get_set($db_data));
		*
		*     ...OR...
		*
		*     $db->query("UPDATE users SET ".$db->get_set($db_data)." WHERE user_id = 1");
		*
		* Output:
		*
		*     login = 'jv', email = 'jv@vip.ie', user_id = 1, created = NOW()
		*/
		function get_set($params)
		{
			if( !is_array( $params ) )
			{
				$this->register_error( 'get_set() parameter invalid. Expected array in '.__FILE__.' on line '.__LINE__);
				return;
			}
			$sql = array();
			foreach ( $params as $field => $val )
			{
				if ( $val === 'true' || $val === true )
					$val = 1;
				if ( $val === 'false' || $val === false )
					$val = 0;

				switch( $val ){
					case 'NOW()' :
					case 'NULL' :
					  $sql[] = "$field = $val";
						break;
					default :
						$sql[] = "$field = '".$this->escape( $val )."'";
				}
			}
			return implode( ', ' , $sql );
		}

		/**
		 * Function for operating query count
		 *
		 * @param bool $all Set to false for function to return queries only during this connection
		 * @param bool $increase Set to true to increase query count (internal usage)
		 * @return int Returns query count base on $all
		 */
		function count ($all = true, $increase = false) {
			if ($increase) {
				$this->num_queries++;
				$this->conn_queries++;
			}

			return ($all) ? $this->num_queries : $this->conn_queries;
		}

    /**
     * Returns, whether a database connection is established, or not
     *
     * @return boolean
     */
    function isConnected() {
        return $this->_connected;
    } // isConnected

    /**
     * Returns the current show error state
     *
     * @return boolean
     */
    function getShowErrors() {
        return $this->show_errors;
    } // getShowErrors

    /**
     * Returns the affected rows of a query
     * 
     * @return int
     */
    function affectedRows() {
        return $this->_affectedRows;
    } // affectedRows
	
	// query call template
    function query($query, $use_prepare=false) {
		return false;
	}    
	
	// escape call template if not available by vendor
	function escape($data) {
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
        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

        return str_replace($search, $replace, $data);
	}
        
} // ezSQLcore
