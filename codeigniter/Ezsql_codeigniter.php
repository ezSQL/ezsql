<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ezSQL Database specific class - codeigniter
 * Desc..: codeigniter component (part of ezSQL databse abstraction library)
 *
 * @author  Justin Vincent (jv@jvmultimedia.com)
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @link    http://twitter.com/justinvincent
 * @name    ezSQL_codeigniter
 * @package ezSQL
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 *
 */
class ezSQL_codeigniter extends ezSQLcore
{
    /**
     * Run in debug mode
     * @var boolean Default is true
     */
    public $debug = true;

    /**
     * Show errors
     * @var boolean Default is true
     */
    public $show_errors = true;

    /**
     *
     * @throws Exception Requires ez_sql_core.php
     */
    function __construct() {
        if ( ! class_exists ('ezSQLcore') ) {
            throw new Exception('<b>Fatal Error:</b> ezSQL requires ezSQLcore (application/helpers/ez_sql_core_helper.php) to be included/loaded before it can be used');
        }
        
        global $db;
        $db = $this;
        $this->CI =& get_instance();
    } // __construct

    /**
     * Perform codignitor query and try to determine result value
     *
     * @param string $query
     * @return boolean|int 
     */
    public function query($query) {
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
        if ( $cache = $this->get_cache($query) ) {
            // Keep tack of how long all queries have taken
            $this->timer_update_global($this->num_queries);

            // Trace all queries
            if ( $this->use_trace_log ) {
                $this->trace_log[] = $this->debug(false);
            }

            return $cache;
        }

        // Perform the query via CI database system
        $ci_query = $this->CI->db->query($query);

        // If there is an error then take note of it..
        if ( $str = $this->CI->db->_error_message() ) {
            $this->register_error($str);
            $this->show_errors ? trigger_error($str, E_USER_WARNING) : null;				

            // If debug ALL queries
            $this->trace || $this->debug_all ? $this->debug() : null ;

            return false;
        }

        // Query was write (insert/delete/update etc.) query?
        $is_insert = false;

        if ( preg_match("/^(insert|delete|update|replace|truncate|drop|create|alter)\s+/i", $query) ) {
            $this->rows_affected = $this->CI->db->affected_rows();

            // Take note of the insert_id
            if ( preg_match("/^(insert|replace)\s+/i", $query) )
            {
                $this->insert_id = $this->CI->db->insert_id();
            }

            // Return number fo rows affected
            $return_val = $this->rows_affected;
        } else {
            // Query was a select
            // Store Query Results
            $num_rows=0;
            if ( $ci_query->num_rows() ) {
                foreach ($ci_query->result() as $row) {
                    // Take note of column info
                    if ( $num_rows == 0 ) {
                        $i = 0;
                        foreach ( get_object_vars($row) as $k => $v ) {
                            $this->col_info[$i]->name = $k;
                            $this->col_info[$i]->max_length = $k;
                            $this->col_info[$i]->type = '';
                            $i++;
                        }
                    }

                    // Store relults as an objects within main array
                    $this->last_result[$num_rows] = $row;
                    $num_rows++;
                }
            }

            // Log number of rows the query returned
            $return_val = $this->num_rows = $num_rows;

        }

        // disk caching of queries
        $this->store_cache($query, $is_insert);

        // If debug ALL queries
        $this->trace || $this->debug_all ? $this->debug() : null ;

        // Keep tack of how long all queries have taken
        $this->timer_update_global($this->num_queries);

        // Trace all queries
        if ( $this->use_trace_log ) {
            $this->trace_log[] = $this->debug(false);
        }

        return $return_val;
    } // query

    /**
     * Format a sql string correctly for safe insert
     *
     * @param string $str
     * @param boolean $like
     * @return string
     */
    public function escape($str, $like=false) {
        return $this->CI->db->escape_str(stripslashes($str), $like = false);
    } // escape

} // ezSQL_codeigniter