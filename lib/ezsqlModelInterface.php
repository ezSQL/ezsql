<?php
namespace ezsql;

/**
 * @method void setDebug_All($args);
 * @method void setTrace($args);
 * @method void setDebug_Called($args);
 * @method void setVarDump_Called($args);
 * @method void setShow_Errors($args);
 * @method void setNum_Queries($args);
 * @method void setConn_Queries($args);
 * @method void setCaptured_Errors($args);
 * @method void setCache_Dir($args);
 * @method void setUse_Disk_Cache($args);
 * @method void setCache_Timeout($args);
 * @method void setCache_Queries($args);
 * @method void setCache_Inserts($args);
 * @method void setNum_Rows($args);
 * @method void setDb_Connect_Time($args);
 * @method void setSql_Log_File($args);
 * @method void setProfile_Times($args);
 * @method void setInsert_Id($args);
 * @method void setLast_Query($args);
 * @method void setLast_Error($args);
 * @method void setCol_Info($args);
 * @method void setTimers($args);
 * @method void setTotal_Query_Time($args);
 * @method void setTrace_Log($args);
 * @method void setUse_Trace_Log($args);
 * @method void setDo_Profile($args);
 * @method void setLast_Result($args);
 * @method void setFrom_Disk_Cache($args);
 * @method void setDebug_Echo_Is_On($args);
 * @method void setFunc_Call($args);
 * @method void setAll_Func_Calls($args);
 *
 * @method string getDebug_All();
 * @method string getTrace();
 * @method string getDebug_Called();
 * @method string getVarDump_Called();
 * @method string getShow_Errors();
 * @method string getNum_Queries();
 * @method string getConn_Queries();
 * @method string getCaptured_Errors();
 * @method string getCache_Dir();
 * @method string getUse_Disk_Cache();
 * @method string getCache_Timeout();
 * @method string getCache_Queries();
 * @method string getCache_Inserts();
 * @method string getNum_Rows();
 * @method string getDb_Connect_Time();
 * @method string getSql_Log_File();
 * @method string getProfile_Times();
 * @method string getInsert_Id();
 * @method string getLast_Query();
 * @method string getLast_Error();
 * @method string getCol_Info();
 * @method string getTimers();
 * @method string getTotal_Query_Time();
 * @method string getTrace_Log();
 * @method string getUse_Trace_Log();
 * @method string getDo_Profile();
 * @method string getLast_Result();
 * @method string getFrom_Disk_Cache();
 * @method string getDebug_Echo_Is_On();
 * @method string getFunc_Call();
 * @method string getAll_Func_Calls();
 */
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
	 * Turn error output to browser on.
	 * 
	 * - If you have not used the function `$db->hide_errors()` this function (show_errors) 
	 * will have no effect.
	 */
	public function show_errors();
	
	/**
	 * Turn error output to browser off.
	 * 
	 * Stops error output from being printed to the web client. 
	 * - If you would like to stop error output but still be able to trap errors for debugging
	 * or for your own error output function you can make use of the global error array $captured_errors. access by calling `$db->getCaptured_Errors()`
	 */
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
	 * Get one variable, from one row, from the database (or previously cached results).
	 * 
	 * This function is very useful for evaluating query results within logic statements such as if or switch. 
	 * - If the query generates more than one row the first row will always be used by default. 
	 * - If the query generates more than one column the leftmost column will always be used by default. 
	 * - Even so, the full results set will be available within the 
	 * 	array `$db->last_results` should you wish to use them.
	 * 
     * @param string $query
     * @param int $x - column offset
     * @param int $y - row offset
     * @return bool|mixed
	 */
	public function get_var(string $query = null, int $x = 0, int $y = 0, 
		bool $use_prepare = false);
	
	/**
	 * Get one row from the database (or previously cached results)
	 * 
	 * - If the query returns more than one row and no row offset is
	 * supplied the first row within the results set will be returned by
	 * default. 
	 * - Even so, the full results will be cached should you wish
	 * to use them with another ezSQL query.
	 * 
     * @param string $query
     * @param OBJECT|ARRAY_A|ARRAY_N $output
     * @param int $y - row offset
     * @return bool|mixed
	 */
	public function get_row(string $query = null, $output = \OBJECT, int $y = 0, 
		bool $use_prepare = false);
	
	/**
	 * Get one column from query (or previously cached results) based on column offset
	 * 
	 * Extracts one column as one dimensional array based on a column 
	 * offset. 
	 * - If no offset is supplied the offset will default to column 0. I.E the first column. 
	 * - If a null query is supplied the previous query results are used.
	 * 
	 * @param string $query
     * @param int $x - column offset
     * @param bool $use_prepare - has prepare statements been activated
     * @return bool|mixed
	 */
	public function get_col(string $query = null, int $x = 0, 
		bool $use_prepare = false);
	
	/**
	 * Get multiple row result set from the database 
	 * (or previously cached results), based on query and returns them as 
	 * a multi dimensional array.
	 * 
	 * Each element of the array contains one row of results and can be
	 * specified to be either an object, associative array or numerical 
	 * array. 
	 * - If no results are found then the function returns `false`,
	 * enabling you to use the function within logic statements such as if.
	 * 
	 * - if setup/active, `prepareActive()` has been called, use
	 * prepare statements in SQL transactions.
	 * 
	 * `Returning results as an object` is the quickest way to get and 
	 * display results. It is also useful that you are able to put 
	 * `$object->var` syntax directly inside print statements without
	 * having to worry about causing php parsing errors.
	 * 
	 * `Returning results as an associative array` is useful if you would
	 * like dynamic access to column names.
	 * 
	 * `Returning results as a numerical array` is useful if you are using
	 * completely dynamic queries with varying column names but still need
	 * a way to get a handle on the results. 
	 * 
	 * @param string $query
     * @param OBJECT|ARRAY_A|ARRAY_N $output
     * @param bool $use_prepare - has prepare statements been activated
     * @return bool|mixed - results as objects (default)
	 */
	public function get_results(string $query = null, $output = \OBJECT, 
		bool $use_prepare = false);
	
	/**
	 * Get information about one or all columns such as column name or type.
	 * 
	 * Returns meta information about one or all columns such as column name or type. 
	 * - If no information type is supplied then the default information type of name is used.
	 * - If no column offset is supplied then a one dimensional array is returned with the
	 * information type for ‘all columns’. 
	 * - For access to the full meta information for all columns you can use the cached 
	 * variable `$db->col_info`, access by calling `$db->getCol_Info()`
	 * 
	 * Available Info-Types:
	 * mySQL
	 * - name - column name
	 * - table - name of the table the column belongs to
	 * - max_length - maximum length of the column
	 * - not_null - 1 if the column cannot be NULL
	 * - primary_key - 1 if the column is a primary key
	 * - unique_key - 1 if the column is a unique key
	 * - multiple_key - 1 if the column is a non-unique key
	 * - numeric - 1 if the column is numeric
	 * - blob - 1 if the column is a BLOB
	 * - type - the type of the column
	 * - unsigned - 1 if the column is unsigned
	 * - zerofill - 1 if the column is zero-filled
	 * 
	 * MS-SQL / Oracle / PostgresSQL
	 * - name - column name 
	 * - type - the type of the column
	 * - length - size of column
	 * 
	 * SQLite
	 * - name - column name  
	 * 
	 * @param string $info_type
     * @param int $col_offset
     * @return mixed
	 */
	public function get_col_info(string $info_type = "name", int $col_offset = -1);
	
	/**
	 * store_cache
	 */
	public function store_cache(string $query, bool $is_insert);
	
	/**
	 * get_cache
	 */
	public function get_cache(string $query);
	
	/**
	 * Dumps the contents of any input variable to screen in a nicely
	 * formatted and easy to understand way.
	 * 
	 * @param mixed $mixed- any type Object, public or Array
	 * @return string
	 */
	public function varDump($mixed = null);
	
	/**
	 * @internal alias for varDump()
	 */
	public function dump_var($mixed = null);
	
	/**
	 * Displays the last sql query and returned results (if any)
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
