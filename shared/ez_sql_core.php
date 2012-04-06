<?php
/**
 * ezSQL Core module - database abstraction library to make it very easy
 * to deal with databases. ezSQLcore can not be used by  itself
 * (it is designed for use by database specific modules).
 *
 * @author  Justin Vincent (jv@vip.ie)
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @link    http://justinvincent.com
 * @name    ezSQL
 * @package ezSQL
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 */
class ezSQLcore
{
    /**
     * Constant string ezSQL version information
     */
    const EZSQL_VERSION = '3.0';

    /**
     * Constant boolean Object
     */
    const OBJECT = true;

    /**
     *  Constant boolean
     */
    const ARRAY_A = true;

    /**
     *  Constant boolean
     */
    const ARRAY_N = true;

    /**
     * same as $debug_all
     * @var boolean Default is false
     */
    public $trace = false;

    /**
     * same as $trace
     * @var boolean Default is false
     */
    public $debug_all        = false;

    /**
     * Debug is called
     * @public boolean Default is false
     */
    public $debug_called     = false;

    /**
     * Vardump called
     * @var boolean Default is false
     */
    public $vardump_called   = false;

    /**
     * Show errors
     * @public boolean Default is false
     */
    private $show_errors      = true;

    /**
     * Number of queries
     * @var int Default is 0
     */
    public $num_queries      = 0;

    /**
     * The last query object
     * @var object Default is null
     */
    public $last_query       = null;

    /**
     * The last error object
     * @var object Default is null
     */
    public $last_error       = null;

    /**
     * The last column info
     * @var object Default is null
     */
    public $col_info         = null;

    /**
     * Captured errors
     * @var array Default is empty array
     */
    public $captured_errors  = array();

    /**
     * Using the cache directory
     * @var boolean Default is false
     */
    public $cache_dir        = false;

    /**
     * Caching queries
     * @var boolean Default is false
     */
    public $cache_queries    = false;

    /**
     * Insert queries into the cache
     * @var boolean Default is false
     */
    public $cache_inserts    = false;

    /**
     * Using disk cache
     * @var boolean Default is false
     */
    public $use_disk_cache   = false;

    /**
     * The cache timeout in hours
     * @var integer Default is 24
     */
    public $cache_timeout    = 24;

    /**
     * Timers
     * @var array Default is empty array
     */
    public $timers           = array();

    /**
     * The total query time
     * @var int Default is 0
     */
    public $total_query_time = 0;

    /**
     * The time it took to establish a connection
     * @var int Default is 0
     */
    public $db_connect_time  = 0;

    /**
     * The trace log
     * @var array Default is empty array
     */
    public $trace_log        = array();

    /**
     * Use the trace log
     * @var boolean Default is false
     */
    public $use_trace_log    = false;

    /**
     * Use a SQL log file
     * @var boolean Default is false
     */
    public $sql_log_file     = false;

    /**
     * Using profiling
     * @var boolean Default is false
     */
    public $do_profile       = false;

    /**
     * Array for storing profiling times
     * @var array Default is empty array
     */
    public $profile_times    = array();

    /**
     * The database connection object
     * @var object Default is null
     */
    public $dbh = null;

    /**
     * Whether the database connection is established, or not
     * @var boolean Default is false
     */
    protected $connected = false;

    /**
     * == TJH == default now needed for echo of debug function
     * The default for returning errors, turn it of, if you are not
     * interested in seeing your database errors
     * @var boolean Default is true
    */
    public $debug_echo_is_on = true;

    /**
     * The last query result
     * @var object Default is null
     */
    public $last_result = null;

    /**
     * Get data from disk cache
     * @var boolean Default is false
     */
    public $from_disk_cache = false;

    /**
     * Function called
     * @var string
     */
    private $func_call;

    /**
     * Constructor of ezSQL
     */
    public function __construct() {

    } // __construct

    /**
     *  Print SQL/DB error - over-ridden by specific DB class
     *
     * @param $err_str string
     */
    public function register_error($err_str) {
        // Keep track of last error
        $this->last_error = $err_str;

        // Capture all errors to an error array no matter what happens
        $this->captured_errors[] = array
        (
            'error_str' => $err_str,
            'query'     => $this->last_query
        );
    } // register_error

    /**
     * Turn error handling on, by default error handling is on
     */
    public function show_errors() {
        $this->show_errors = true;
    } // show_errors

    /**
     *  Turn error handling off
     */
    public function hide_errors() {
        $this->show_errors = false;
    } // hide_errors

    /**
     * Kill cached query results
     */
    public function flush() {
        // Get rid of these
        $this->last_result = null;
        $this->col_info = null;
        $this->last_query = null;
        $this->from_disk_cache = false;
    } // flush

    /**
     * Get one variable from the DB - see docs for more detail
     *
     * @param $query object A query object, default is null
     * @param $x int Default is 0
     * @param $y int Default is 0
     * @return variant The value of a variable
     */
    public function get_var($query=null, $x=0, $y=0) {
        // Log how the function was called
        $this->func_call = "\$db->get_var(\"$query\",$x,$y)";

        // If there is a query then perform it if not then use cached results..
        if ( $query ) {
            $this->query($query);
        }

        // Extract public out of cached results based x,y vals
        if ( $this->last_result[$y] ) {
            $values = array_values(get_object_vars($this->last_result[$y]));
        }

        // If there is a value return it else return null
        return (isset($values[$x]) && $values[$x]!=='')?$values[$x]:null;
    } // get_var

    /**
     * Get one row from the DB - see docs for more detail
     *
     * @param object $query Default is null
     * @param bolean $output Default is the OBJECT constant
     * @param int $y Default is 0
     * @return type
     */
    public function get_row($query=null, $output=self::OBJECT, $y=0) {
        // Log how the function was called
        $this->func_call = "\$db->get_row(\"$query\",$output,$y)";

        // If there is a query then perform it if not then use cached results..
        if ( $query ) {
            $this->query($query);
        }

        // If the output is an object then return object using the row offset..
        if ( $output == self::OBJECT ) {
            return $this->last_result[$y] ? $this->last_result[$y] : null;
        }
        // If the output is an associative array then return row as such..
        elseif ( $output == self::ARRAY_A ) {
            return $this->last_result[$y] ? get_object_vars($this->last_result[$y]) : null;
        } elseif ( $output == self::ARRAY_N ) {
            // If the output is an numerical array then return row as such..
            return $this->last_result[$y]?array_values(get_object_vars($this->last_result[$y])):null;
        } else {
            // If invalid output type was specified..
            $this->print_error(" \$db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N");
        }

    } // get_row

    /**
     * Function to get 1 column from the cached result set based in
     * X index
     * see docs for usage and info
     *
     * @param object $query Default is null
     * @param type $x Default is 0
     * @return array
     */
    public function get_col($query=null, $x=0) {

        $new_array = array();

        // If there is a query then perform it if not then use cached results..
        if ( $query ) {
            $this->query($query);
        }

        // Extract the column values
        for ( $i=0; $i < count($this->last_result); $i++ ) {
            $new_array[$i] = $this->get_var(null, $x, $i);
        }

        return $new_array;
    } // get_col


    /**
     * Return the the query as a result set - see docs for more
     * details
     *
     * @param object $query Default is null
     * @param boolean $output Default is the OBJECT constant
     * @return array
     */
    public function get_results($query=null, $output=self::OBJECT) {

        // Log how the function was called
        $this->func_call = "\$db->get_results(\"$query\", $output)";

        // If there is a query then perform it if not then use cached results..
        if ( $query ) {
            $this->query($query);
        }

        // Send back array of objects. Each row is an object
        if ( $output == self::OBJECT ) {
            return $this->last_result;
        } elseif ( $output == self::RAY_A || $output == self::ARRAY_N ) {
            if ( $this->last_result ) {
                $i=0;
                foreach( $this->last_result as $row ) {

                    $new_array[$i] = get_object_vars($row);

                    if ( $output == self::ARRAY_N ) {
                        $new_array[$i] = array_values($new_array[$i]);
                    }

                    $i++;
                }

                return $new_array;
            } else {
                return null;
            }
        }
    } // get_results


    /**
     * Function to get column meta data info pertaining to the last
     * query
     * See docs for more info and usage
     *
     * @param type $info_type
     * @param type $col_offset
     * @return type
     */
    public function get_col_info($info_type='name', $col_offset=-1) {
        $new_array = array();

        if ( $this->col_info ) {
            if ( $col_offset == -1 ) {
                $i=0;
                foreach($this->col_info as $col ) {
                    $new_array[$i] = $col->{$info_type};
                    $i++;
                }
                return $new_array;
            } else {
                return $this->col_info[$col_offset]->{$info_type};
            }
        }

    } // get_col_info

    /**
     * Store the cache
     *
     * @param object $query
     * @param boolean $is_insert
     */
    public function store_cache($query, $is_insert) {

        // The would be cache file for this query
        $cache_file = $this->cache_dir.'/'.md5($query);

        // disk caching of queries
        if ( $this->use_disk_cache && ( $this->cache_queries && ! $is_insert ) || ( $this->cache_inserts && $is_insert )) {
            if ( ! is_dir($this->cache_dir) ) {
                $this->register_error("Could not open cache dir: $this->cache_dir");
                $this->show_errors ? trigger_error("Could not open cache dir: $this->cache_dir",E_USER_WARNING) : null;
            } else {
                // Cache all result values
                $result_cache = array
                    (
                        'col_info' => $this->col_info,
                        'last_result' => $this->last_result,
                        'num_rows' => $this->num_rows,
                        'return_value' => $this->num_rows,
                    );
                error_log ( serialize($result_cache), 3, $cache_file);
            }
        }
    } // store_cache

    /**
     * Get the query cache of a query
     *
     * @param object $query
     * @return object
     */
    public function get_cache($query) {
        // The would be cache file for this query
        $cache_file = $this->cache_dir.'/'.md5($query);

        // Try to get previously cached version
        if ( $this->use_disk_cache && file_exists($cache_file) ) {
            // Only use this cache file if less than 'cache_timeout' (hours)
            if ( (time() - filemtime($cache_file)) > ($this->cache_timeout*3600) ) {
                unlink($cache_file);
            } else {
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
    } // get_cache

    /**
     * Dumps the contents of any input variable to screen in a nicely formatted
     * and easy to understand way - any type: Object, public or Array
     *
     * @param variant $mixed Default is empty String
     * @return string Returns HTML result
     */
    public function vardump($mixed='') {
        // Start outup buffering
        ob_start();

        echo "<p><table><tr><td bgcolor=ffffff><blockquote><font color=000090>";
        echo "<pre><font face=arial>";

        if ( ! $this->vardump_called ) {
            echo "<font color=800080><b>ezSQL</b> (v" . self::EZSQL_VERSION . ") <b>Variable Dump..</b></font>\n\n";
        }

        $var_type = gettype ($mixed);
        print_r(($mixed?$mixed:"<font color=red>No Value / False</font>"));
        echo "\n\n<b>Type:</b> " . ucfirst($var_type) . "\n";
        echo "<b>Last Query</b> [$this->num_queries]<b>:</b> ".($this->last_query?$this->last_query:"NULL")."\n";
        echo "<b>Last Function Call:</b> " . ($this->func_call ? $this->func_call : "None")."\n";
        echo "<b>Last Rows Returned:</b> ".count($this->last_result)."\n";
        echo "</font></pre></font></blockquote></td></tr></table>".$this->donation();
        echo "\n<hr size=1 noshade color=dddddd>";

        // Stop output buffering and capture debug HTML
        $html = ob_get_contents();
        ob_end_clean();

        // Only echo output if it is turned on
        if ( $this->debug_echo_is_on ) {
            echo $html;
        }

        $this->vardump_called = true;

        return $html;
    } // vardump

    /**
     * An alias for vardump method
     *
     * @param variant $mixed Default is empty String
     * @return string Returns HTML result
     */
    public function dumpvar($mixed) {
        return $this->vardump($mixed);
    } // dumpvar

    /**
     * Displays the last query string that was sent to the database & a table
     * listing results (if there were any).
     * (Abstracted into a seperate files to save server overhead).
     *
     * @param boolean $print_to_screen Default is true
     * @return string The HTML result
     */
    public function debug($print_to_screen=true) {
        // Start outup buffering
        ob_start();

        echo "<blockquote>";

        // Only show ezSQL credits once..
        if ( ! $this->debug_called ) {
            echo "<font color=800080 face=arial size=2><b>ezSQL</b> (v". self::EZSQL_VERSION .") <b>Debug..</b></font><p>\n";
        }

        if ( $this->last_error ) {
            echo "<font face=arial size=2 color=000099><b>Last Error --</b> [<font color=000000><b>$this->last_error</b></font>]<p>";
        }

        if ( $this->from_disk_cache ) {
            echo "<font face=arial size=2 color=000099><b>Results retrieved from disk cache</b></font><p>";
        }

        echo "<font face=arial size=2 color=000099><b>Query</b> [$this->num_queries] <b>--</b> ";
        echo "[<font color=000000><b>$this->last_query</b></font>]</font><p>";

        echo "<font face=arial size=2 color=000099><b>Query Result..</b></font>";
        echo "<blockquote>";

        if ( $this->col_info ) {

            // =====================================================
            // Results top rows

            echo "<table cellpadding=5 cellspacing=1 bgcolor=555555>";
            echo "<tr bgcolor=eeeeee><td nowrap valign=bottom><font color=555599 face=arial size=2><b>(row)</b></font></td>";


            for ( $i=0; $i < count($this->col_info); $i++ ) {
                echo "<td nowrap align=left valign=top><font size=1 color=555599 face=arial>{$this->col_info[$i]->type} {$this->col_info[$i]->max_length}</font><br><span style='font-family: arial; font-size: 10pt; font-weight: bold;'>{$this->col_info[$i]->name}</span></td>";
            }

            echo "</tr>";

            // ======================================================
            // print main results

        if ( $this->last_result ) {

            $i=0;
            foreach ( $this->get_results(null, self::ARRAY_N) as $one_row ) {
                $i++;
                echo "<tr bgcolor=ffffff><td bgcolor=eeeeee nowrap align=middle><font size=2 color=555599 face=arial>$i</font></td>";

                foreach ( $one_row as $item ) {
                    echo "<td nowrap><font face=arial size=2>$item</font></td>";
                }

                echo "</tr>";
            }

        }  else {
            // if last result
            echo "<tr bgcolor=ffffff><td colspan=".(count($this->col_info)+1)."><font face=arial size=2>No Results</font></td></tr>";
        }

        echo "</table>";

        }  else {
            // if col_info
            echo "<font face=arial size=2>No Results</font>";
        }

        echo "</blockquote></blockquote>".$this->donation()."<hr noshade color=dddddd size=1>";

        // Stop output buffering and capture debug HTML
        $html = ob_get_contents();
        ob_end_clean();

        // Only echo output if it is turned on
        if ( $this->debug_echo_is_on && $print_to_screen) {
            echo $html;
        }

        $this->debug_called = true;

        return $html;
    } // debug

    /**
     * Naughty little function to ask for some remuniration!
     *
     * @return string An HTML string with payment information
     */
    public function donation() {
        $return_val = '<span font-size:x-small; font-family:arial, sans-serif; color:000000;>'
                    . 'If ezSQL has helped <a href="https://www.paypal.com/xclick/business=justin%40justinvincent.com&item_name=ezSQL&no_note=1&tax=0" '
                    . 'style=\"color: 0000CC;">make a donation!?</a> &nbsp;&nbsp;'
                    . '<!--[ go on! you know you want to! ]--></span>';

        return $return_val;
    } // donation

    /***************************************************************************
    *  Timer related functions
    ***************************************************************************/

    /**
     * Get current time
     *
     * @return float
     */
    public function timer_get_cur() {
        list($usec, $sec) = explode(' ',microtime());
        return ((float)$usec + (float)$sec);
    } // timer_get_cur

    /**
     * Start a timer by name
     *
     * @param string $timer_name
     */
    public function timer_start($timer_name) {
        $this->timers[$timer_name] = $this->timer_get_cur();
    } // timer_start

    /**
     * Returns the elapsed time of the given timer by name
     *
     * @param string $timer_name
     * @return float
     */
    public function timer_elapsed($timer_name) {
        return round($this->timer_get_cur() - $this->timers[$timer_name],2);
    } // timer_elapsed

    /**
     * Update the global timer with an existing timer
     *
     * @param string $timer_name
     */
    public function timer_update_global($timer_name) {
        if ( $this->do_profile ) {
            $this->profile_times[] = array
                (
                    'query' => $this->last_query,
                    'time' => $this->timer_elapsed($timer_name)
                );
        }

        $this->total_query_time += $this->timer_elapsed($timer_name);
    } // timer_update_global

    /**
     * Returns, whether a database connection is established, or not
     *
     * @return boolean
     */
    public function isConnected() {
        return $this->connected;
    } // isConnected

    /**
     * Returns the current show error state
     *
     * @return boolean
     */
    public function getShowErrors() {
        return $this->show_errors;
    } // getShowErrors

} // ezSQLcore