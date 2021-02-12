<?php

namespace ezsql;

interface DatabaseInterface
{
    /**
     * Database configuration methods:
     *
     * - getDriver();
     * - getDsn();
     * - getUser();
     * - getPassword()
     * - getName();
     * - getHost();
     * - getPort();
     * - getCharset();
     * - getOptions();
     * - getIsFile();
     * - getToMssql();
     * - getToMysql();
     * - getPath();
     *---
     * - setDriver($args);
     * - setDsn($args);
     * - setUser($args);
     * - setPassword($args);
     * - setName($args);
     * - setHost($args);
     * - setPort($args);
     * - setCharset($args);
     * - setOptions($args);
     * - setIsFile($args);
     * - setToMssql($args);
     * - setToMysql($args);
     * - setPath($args);
     *
     * @return string|array|bool|object
     */
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
     * Creates a prepared query, binds the given parameters and returns the result of the executed.
     *
     * Supplying the the whole query string, and placing '?' within,
     * then the same number of arguments in an array.
     *
     * Example:
     *  `query_prepared('INSERT INTO unit_test(id, test_key) VALUES(1, ?)', ['test 1']);`
     *
     * - It will determine arguments type, execute, and return results.
     *
     * @param string $query
     * @param array $param
     * @return bool|mixed
     */
    public function query_prepared(string $query, array $param = null);

    /**
     * Perform SQL query and try to determine result value.
     *
     * It should be noted that you can send any type of query to the
     * database using this command.
     *
     * If there are any results generated they will be stored and can be
     * accessed by any ezsql function as long as you use a null query.
     *
     * If there are results returned the function will return `true`.
     * if no results the return will be `false`.
     *
     * @param string $query
     * @param bool $use_prepare
     * @return bool|mixed
     */
    public function query(string $query, bool $use_prepare = false);

    /**
     * Close the database connection
     */
    public function disconnect();

    /**
     * Get connection handle
     */
    public function handle();

    /**
     * Reset connection handle
     */
    public function reset();

    /**
     * Begin Transaction
     */
    public function beginTransaction();

    /**
     * Commit Transaction
     */
    public function commit();

    /**
     * Rollback Transaction
     */
    public function rollback();
}
