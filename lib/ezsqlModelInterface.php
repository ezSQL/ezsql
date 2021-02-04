<?php

namespace ezsql;

interface ezsqlModelInterface
{
	/**
	 * Get host and port from an "host:port" notation.
	 *
	 * @param string $host
	 * @param bool $default
	 * @return array of host and port. If port is omitted, returns $default
	 */
	public function get_host_port(string $host, bool $default = false);

	/**
	 * Store Query/SQL/DB error - over-ridden by specific DB class
	 *
	 * @param string $err_str
	 * @param bool $displayError
	 * @return bool
	 * @throws Exception
	 */
	public function register_error(string $err_str, bool $displayError = true);

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
	 *
	 * @param string $query
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
	public function get_var(string $query = null, int $x = 0, int $y = 0, bool $use_prepare = false);

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
	public function get_row(string $query = null, $output = \OBJECT, int $y = 0, bool $use_prepare = false);

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
	public function get_col(string $query = null, int $x = 0, bool $use_prepare = false);

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
	public function get_results(string $query = null, $output = \OBJECT, bool $use_prepare = false);

	/**
	 * Get information about one or all columns such as column name or type.
	 *
	 * Returns meta information about one or all columns such as column name or type.
	 * - If no information type is supplied then the default information type of name is used.
	 * - If no column offset is supplied then a one dimensional array is returned with the
	 * information type for â€˜all columnsâ€™.
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
	 * create cache directory if doesn't exists
	 *
	 * @param string $path
	 */
	public function create_cache(string $path = null);

	/**
	 * Store cache
	 *
	 * @param string $query
	 * @param bool $is_insert
	 */
	public function store_cache(string $query, bool $is_insert);

	/**
	 * Get stored cache
	 *
	 * @param string $query
	 * @return mixed
	 */
	public function get_cache(string $query);

	/**
	 * Dumps the contents of any input variable to screen in a nicely
	 * formatted and easy to understand way.
	 *
	 * @param mixed $mixed- any type Object, public or Array
	 * @return string|void
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

	/**
	 * Returns the last query result
	 *
	 * @return array
	 */
	public function queryResult();
}
