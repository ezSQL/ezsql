<?php
namespace ezsql;

interface ezsqlModelInterface
{
    /**
	* Get host and port from an "host:port" notation.
	* @return array of host and port. If port is omitted, returns $default
	*/
	public function get_host_port( $host, $default = false );
	
	/**
	* Print SQL/DB error - over-ridden by specific DB class
	*/
	public function register_error($err_str);
	
	/**
	* Turn error handling on or off..
	*/
	public function show_errors();
	
	public function hide_errors();
	
	/**
	* Kill cached query results
	*/
	public function flush();
	
	/**
	* Log how the query function was called
	* @param string
	*/
	public function log_query(string $query);
	
	/**
	* Get one variable from the DB - see docs for more detail
	*/
	public function get_var(string $query = null, $x = 0, $y = 0, $use_prepare = false);
	
	/**
	* Get one row from the DB - see docs for more detail
	*/
	public function get_row(string $query = null, $output = OBJECT, $y = 0, $use_prepare = false);
	
	/**
	* Function to get 1 column from the cached result set based in X index
	* see docs for usage and info
	*/
	public function get_col(string $query = null, $x = 0, $use_prepare = false);
	
	/**
	* Return the the query as a result set, will use prepare statements if setup - see docs for more details
	*/
	public function get_results(string $query = null, $output = OBJECT, $use_prepare = false);
	
	/**
	* Function to get column meta data info pertaining to the last query
	* see docs for more info and usage
	*/
	public function get_col_info($info_type = "name", $col_offset = -1);
	
	/**
	* store_cache
	*/
	public function store_cache(string $query, $is_insert);
	
	/**
	* get_cache
	*/
	public function get_cache(string $query);
	
	/**
	* Dumps the contents of any input variable to screen in a nicely
	* formatted and easy to understand way - any type: Object, public or Array
	* @param mixed $mixed
	* @return string
	*/
	public function varDump($mixed = '');
	
	/**
	* @internal alias for varDump()
	*/
	public function dump_var($mixed = '');
	
	/**
	* Displays the last query string that was sent to the database & a
	* table listing results (if there were any).
	* (abstracted into a separate file to save server overhead).
	*
	* @param boolean $print_to_screen
	* @return string
	*/
	public function debug($print_to_screen = true);
	
	/**
	* Timer related functions
	*/
	public function timer_get_cur();
	
	public function timer_start($timer_name);
	
	public function timer_elapsed($timer_name);
	
	public function timer_update_global($timer_name);
	
	public function get_set($params);
	
	/**
	* Function for operating query count
	*
	* @param bool $all Set to false for function to return queries only during this connection
	* @param bool $increase Set to true to increase query count (internal usage)
	* @return int Returns query count base on $all
	*/
	public function count($all = true, $increase = false);

    /**
     * Returns, whether a database connection is established, or not
     *
     * @return boolean
     */
	public function isConnected(); 

    /**
     * Returns the affected rows of a query
     * 
     * @return int
     */
	public function affectedRows();
}
