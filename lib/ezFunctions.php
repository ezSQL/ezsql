<?php

use ezsql\ezQuery;
use ezsql\ezSchema;
use ezsql\Database;
use ezsql\DatabaseInterface;
use ezsql\Database\ez_pdo;

// Global class instances, will be used to call methods directly here.
global $ezInstance;

if (!function_exists('ezFunctions')) {
    function database(string $sqlDriver = null, array $connectionSetting = null, string $instanceTag = null)
    {
        return Database::initialize($sqlDriver, $connectionSetting, $instanceTag);
    }

    function tagInstance(string $getTag = null)
    {
        return \database($getTag);
    }

    function mysqlInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return \database(\MYSQLI, $databaseSetting, $instanceTag);
    }

    function pgsqlInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return \database(\PGSQL, $databaseSetting, $instanceTag);
    }

    function mssqlInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return \database(\MSSQL, $databaseSetting, $instanceTag);
    }

    function pdoInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return \database(\Pdo, $databaseSetting, $instanceTag);
    }

    function sqliteInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return \database(\SQLITE3, $databaseSetting, $instanceTag);
    }

    function getVendor()
    {
        return ezSchema::vendor();
    }

    function to_string($arrays, $separation = ',')
    {
        return ezQuery::to_string($arrays, $separation);
    }

    function column(string $column = null, string $type = null, ...$args)
    {
        return ezSchema::column($column, $type, ...$args);
    }

    function primary(string $constraintName, ...$primaryKeys)
    {
        array_unshift($primaryKeys, \PRIMARY);
        return \column(\CONSTRAINT, $constraintName, ...$primaryKeys);
    }

    function foreign(string $constraintName, ...$foreignKeys)
    {
        array_unshift($foreignKeys, \FOREIGN);
        return \column(\CONSTRAINT, $constraintName, ...$foreignKeys);
    }

    function unique(string $constraintName, ...$uniqueKeys)
    {
        array_unshift($uniqueKeys, \UNIQUE);
        return \column(\CONSTRAINT, $constraintName, ...$uniqueKeys);
    }

    function index(string $indexName, ...$indexKeys)
    {
        return \column(\INDEX, $indexName, ...$indexKeys);
    }

    function addColumn(string $columnName, ...$datatype)
    {
        return \column(\ADD, $columnName, ...$datatype);
    }

    function dropColumn(string $columnName, ...$data)
    {
        return \column(\DROP, $columnName, ...$data);
    }

    function createCertificate(
        string $privatekeyFile = 'certificate.key', 
        string $certificateFile = 'certificate.crt', 
        string $signingFile = 'certificate.csr', 
        // string $caCertificate = null, 
        string $ssl_path = null, 
        array $details = ["commonName" => "localhost"]
    ) 
    {
        return ezQuery::createCertificate($privatekeyFile, $certificateFile, $signingFile, $ssl_path, $details);
    }

	/**
     * Creates an array from expressions in the following format
     * 
     * @param strings $x, - The left expression.
     * @param strings $operator, - One of 
     *      '<', '>', '=', '!=', '>=', '<=', '<>', 'IN',, 'NOT IN', 'LIKE', 
     *      'NOT LIKE', 'BETWEEN', 'NOT BETWEEN', 'IS', 'IS NOT', or  the constants above.
     * 
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * function comparison($x, $operator, $y, $and=null, ...$args)
     *  {
     *          return array($x, $operator, $y, $and, ...$args);
     * }
     * 
     * @return array
     */
    
    /**
     * Creates an equality comparison expression with the given arguments.
     */
    function eq($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \EQ, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a non equality comparison expression with the given arguments.
     */
    function neq($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \NEQ, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates the other non equality comparison expression with the given arguments.
     */
    function ne($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \NE, $y, $and, ...$args);
        return $expression;
    }
    
    /**
     * Creates a lower-than comparison expression with the given arguments.
     */
    function lt($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \LT, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a lower-than-equal comparison expression with the given arguments.
     */
    function lte($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \LTE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a greater-than comparison expression with the given arguments.
     */
    function gt($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \GT, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a greater-than-equal comparison expression with the given arguments.
     */
    function gte($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \GTE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates an IS NULL expression with the given arguments.
     */
    function isNull($x, $y = 'null', $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_isNULL, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates an IS NOT NULL expression with the given arguments.
     */
    function isNotNull($x, $y = 'null', $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_notNULL, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a LIKE() comparison expression with the given arguments.
     */
    function like($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_LIKE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT LIKE() comparison expression with the given arguments.
     */
    function notLike($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_notLIKE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a IN () comparison expression with the given arguments.
     */
    function in($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_IN, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT IN () comparison expression with the given arguments.
     */
    function notIn($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_notIN, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a BETWEEN () comparison expression with the given arguments.
     */
    function between($x, $y, $y2, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_BETWEEN,$y, $y2, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT BETWEEN () comparison expression with the given arguments.
     */
    function notBetween($x, $y, $y2, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_notBETWEEN, $y, $y2, ...$args);
        return $expression;
    }
        
    /**
    * Using global class instances, setup functions to call class methods directly.
    *
    * @return boolean - true, or false for error
    */
    function setInstance($ezSQL = '') {
        global $ezInstance;
        $status = false;

        if ($ezSQL instanceOf DatabaseInterface) {
			$ezInstance = $ezSQL;
            $status = true;
		} 

        return $status;
    }

    function getInstance() {
        global $ezInstance;

        return ($ezInstance instanceOf DatabaseInterface) ? $ezInstance : null;
    }

    function clearInstance() {
        $GLOBALS['ezInstance'] = null;
        unset($GLOBALS['ezInstance']);
    }

    function cleanInput($string) {
        return ezQuery::clean($string);
    } 

    function select($table = '', $columns = '*', ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->selecting($table, $columns, ...$args) 
            : false;
    } 
    
    function select_into($table, $columns = '*', $old = null, ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->select_into($table, $columns, $old, ...$args) 
            : false;
    } 
    
    function insert_select($totable = '', $columns = '*', $fromTable, $from = '*', ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->insert_select($totable, $columns, $fromTable, $from, ...$args) 
            : false;
    }     
    
    function create_select($table, $from, $old = null, ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->create_select($table, $from, $old, ...$args) 
            : false;
    }  
    
    function where( ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->where( ...$args) 
            : false;
    } 
    
    function groupBy($groupBy) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->groupBy($groupBy) 
            : false;
    } 
    
    function having( ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->having( ...$args) 
            : false;
    }

    function innerJoin(
        $leftTable = '',
        $rightTable = '', 
        $leftColumn = null, 
        $rightColumn = null, 
        $condition = \EQ
    ) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->innerJoin($leftTable, $rightTable, $leftColumn, $rightColumn, $condition) 
            : false;
    }

    function leftJoin(
        $leftTable = '',
        $rightTable = '', 
        $leftColumn = null, 
        $rightColumn = null, 
        $condition = \EQ
    ) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->leftJoin($leftTable, $rightTable, $leftColumn, $rightColumn, $condition) 
            : false;
    }

    function rightJoin(
        $leftTable = '',
        $rightTable = '', 
        $leftColumn = null, 
        $rightColumn = null, 
        $condition = \EQ
    ) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->rightJoin($leftTable, $rightTable, $leftColumn, $rightColumn, $condition) 
            : false;
    }

    function fullJoin(
        $leftTable = '',
        $rightTable = '', 
        $leftColumn = null, 
        $rightColumn = null, 
        $condition = \EQ
    ) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->fullJoin($leftTable, $rightTable, $leftColumn, $rightColumn, $condition) 
            : false;
    }

    function union($table = '', $columnFields = '*', ...$conditions) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->union($table, $columnFields, ...$conditions) 
            : false;
    } 

    function unionAll($table = '', $columnFields = '*', ...$conditions) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->unionAll($table, $columnFields, ...$conditions) 
            : false;
    } 

    function orderBy($orderBy, $order) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->orderBy($orderBy, $order) 
            : false;
    } 

    function limit($numberOf, $offset = null) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->limit($numberOf, $offset) 
            : false;
    } 
    
    function insert($table = '', $keyValue) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->insert($table, $keyValue) 
            : false;
    } 
    
    function update($table = '', $keyValue, ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->update($table, $keyValue, ...$args) 
            : false;
    } 
    
    function delete($table = '', ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->delete($table, ...$args) 
            : false;
    } 
        
    function replace($table = '', $keyValue) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf DatabaseInterface) 
            ? $ezQuery->replace($table, $keyValue) 
            : false;
    }  

    function ezFunctions() {
        return true;
    }
}
