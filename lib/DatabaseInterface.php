<?php
namespace ezsql;

interface DatabaseInterface
{
    public function settings();

    /**
     * Format a mySQL string correctly for safe mySQL insert
     * (no matter if magic quotes are on or not)
     *
     * @param string $str
     * @return string
     */
    public function escape(string $str);

    /**
     * Return specific system date syntax
     * i.e. Oracle: SYSDATE Mysql: NOW()
     *
     * @return string
     */
    public function sysDate();
    
	/**
     * Creates a prepared query, binds the given parameters and returns the result of the executed
     * {@link \mysqli_stmt}.
     * @param string $query
     * @param array $args
     * @return bool|mysqli_result
     */
    public function query_prepared($query, array $args);
    
    /**
     * Perform mySQL query and try to determine result value
     *
     * @param type $query
     * @return boolean
     */
    public function query($query, $use_prepare = false);
	
    /**
     * Close the database connection
     */
    public function disconnect();
}
