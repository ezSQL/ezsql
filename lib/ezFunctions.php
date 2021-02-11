<?php

use ezsql\ezQuery;
use ezsql\ezSchema;
use ezsql\Database;
use ezsql\ezQueryInterface;
use ezsql\DatabaseInterface;
use ezsql\Database\ez_pdo;

// Global class instances, will be used to call methods directly here.

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

    function primary(string $primaryName, ...$primaryKeys)
    {
        array_unshift($primaryKeys, \PRIMARY);
        return \column(\CONSTRAINT, $primaryName, ...$primaryKeys);
    }

    function foreign(string $foreignName, ...$foreignKeys)
    {
        array_unshift($foreignKeys, \FOREIGN);
        return \column(\CONSTRAINT, $foreignName, ...$foreignKeys);
    }

    function unique(string $uniqueName, ...$uniqueKeys)
    {
        array_unshift($uniqueKeys, \UNIQUE);
        return \column(\CONSTRAINT, $uniqueName, ...$uniqueKeys);
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
    ) {
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
    function in($x, $y, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_IN, $y, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT IN () comparison expression with the given arguments.
     */
    function notIn($x, $y, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_notIN, $y, ...$args);
        return $expression;
    }

    /**
     * Creates a BETWEEN () comparison expression with the given arguments.
     */
    function between($x, $y, $y2, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_BETWEEN, $y, $y2, \_AND, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT BETWEEN () comparison expression with the given arguments.
     */
    function notBetween($x, $y, $y2, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_notBETWEEN, $y, $y2, \_AND, ...$args);
        return $expression;
    }

    /**
     * Using global class instances, setup functions to call class methods directly.
     *
     * @return boolean - true, or false for error
     */
    function setInstance($ezSQL = '')
    {
        global $ezInstance;
        $status = false;

        if ($ezSQL instanceof ezQueryInterface) {
            $ezInstance = $ezSQL;
            $status = true;
        }

        return $status;
    }

    function getInstance()
    {
        global $ezInstance;

        return ($ezInstance instanceof ezQueryInterface) ? $ezInstance : null;
    }

    function clearInstance()
    {
        $GLOBALS['ezInstance'] = null;
        unset($GLOBALS['ezInstance']);
    }

    function cleanInput($string)
    {
        return ezQuery::clean($string);
    }

    /**
     * Returns an SQL string or result set, given the
     *   - table, column fields, conditions or conditional array.
     *
     * In the following format:
     * ```js
     * select(
     *   table,
     *   columns,
     *    (innerJoin(), leftJoin(), rightJoin(), fullJoin()), // alias of joining(inner|left|right|full, leftTable, rightTable, leftColumn, rightColumn, equal condition),
     *   where( eq( columns, values, _AND ), like( columns, _d ) ),
     *   groupBy( columns ),
     *   having( between( columns, values1, values2 ) ),
     *   orderBy( columns, desc ),
     *   limit( numberOfRecords, offset ),
     *   union(table, columnFields, conditions), // Returns an select SQL string with `UNION`
     *   unionAll(table, columnFields, conditions) // Returns an select SQL string with `UNION ALL`
     *);
     * ```
     * @param $table, - database table to access
     * @param $columnFields, - table columns, string or array
     * @param mixed ...$conditions - of the following parameters:
     *
     *   @param $joins, - join clause (type, left table, right table, left column, right column, condition = EQ)
     *   @param $whereKey, - where clause ( comparison(x, y, and) )
     *   @param $groupBy, - grouping over clause the results
     *   @param $having, - having clause ( comparison(x, y, and) )
     *   @param $orderby, - ordering by clause for the query
     *   @param $limit, - limit clause the number of records
     *   @param $union/$unionAll - union clause combine the result sets and removes duplicate rows/does not remove
     *
     * @return mixed result set - see docs for more details, or false for error
     */
    function select($table = '', $columns = '*', ...$args)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->selecting($table, $columns, ...$args)
            : false;
    }

    /**
     * Does an select into statement by calling selecting method
     * @param $newTable, - new database table to be created
     * @param $fromColumns - the columns from old database table
     * @param $oldTable - old database table
     * @param $fromWhere, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
     *
     * @return mixed bool/result - false for error
     */
    function select_into($table, $columns = '*', $old = null, ...$args)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->select_into($table, $columns, $old, ...$args)
            : false;
    }

    function insert_select($totable = '', $columns = '*', $fromTable = null, $from = '*', ...$args)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->insert_select($totable, $columns, $fromTable, $from, ...$args)
            : false;
    }

    function create_select($table, $from, $old = null, ...$args)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->create_select($table, $from, $old, ...$args)
            : false;
    }

    /**
     * Returns an `WHERE` **sql clause** string.
     *
     * format:
     *   `where( comparison(x, y, and) )`
     *
     * example:
     *   `where( eq(key, value ), like('key', '_%?');`
     *
     * @param array $whereConditions - In the following format:
     *```js
     *   eq('key/Field/Column', $value, _AND), // combine next expression
     *   neq('key/Field/Column', $value, _OR), // will combine next expression if
     *   ne('key/Field/Column', $value), // the default is _AND so will combine next expression
     *   lt('key/Field/Column', $value)
     *   lte('key/Field/Column', $value)
     *   gt('key/Field/Column', $value)
     *   gte('key/Field/Column', $value)
     *   isNull('key/Field/Column')
     *   isNotNull('key/Field/Column')
     *   like('key/Field/Column', '_%')
     *   notLike('key/Field/Column', '_%')
     *   in('key/Field/Column', $values)
     *   notIn('key/Field/Column', $values)
     *   between('key/Field/Column', $value, $value2)
     *   notBetween('key/Field/Column', $value, $value2)
     *```
     * @return mixed bool/string - WHERE sql statement, or false on error
     */
    function where(...$whereConditions)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->where(...$whereConditions)
            : false;
    }

    /**
     * Adds WHERE grouping to the conditions
     *
     * format:
     *   `grouping( comparison(x, y, and) )`
     *
     * example:
     *   `grouping( eq(key, value, combiner ), eq(key, value, combiner ) );`
     *
     * @param array $whereConditions - In the following format:
     *```js
     *   eq('key/Field/Column', $value, _AND), // combine next expression
     *   neq('key/Field/Column', $value, _OR), // will combine next expression again
     *   ne('key/Field/Column', $value), // the default is _AND so will combine next expression
     *   lt('key/Field/Column', $value)
     *   lte('key/Field/Column', $value)
     *   gt('key/Field/Column', $value)
     *   gte('key/Field/Column', $value)
     *   isNull('key/Field/Column')
     *   isNotNull('key/Field/Column')
     *   like('key/Field/Column', '_%')
     *   notLike('key/Field/Column', '_%')
     *```
     * @return array modified conditions
     */
    function grouping(...$args)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->grouping(...$args)
            : false;
    }

    function groupBy($groupBy)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->groupBy($groupBy)
            : false;
    }

    function having(...$args)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->having(...$args)
            : false;
    }

    function innerJoin(
        $leftTable = '',
        $rightTable = '',
        $leftColumn = null,
        $rightColumn = null,
        $tableAs = null,
        $condition = \EQ
    ) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->innerJoin($leftTable, $rightTable, $leftColumn, $rightColumn, $tableAs, $condition)
            : false;
    }

    function leftJoin(
        $leftTable = '',
        $rightTable = '',
        $leftColumn = null,
        $rightColumn = null,
        $tableAs = null,
        $condition = \EQ
    ) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->leftJoin($leftTable, $rightTable, $leftColumn, $rightColumn, $tableAs, $condition)
            : false;
    }

    function rightJoin(
        $leftTable = '',
        $rightTable = '',
        $leftColumn = null,
        $rightColumn = null,
        $tableAs = null,
        $condition = \EQ
    ) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->rightJoin($leftTable, $rightTable, $leftColumn, $rightColumn, $tableAs, $condition)
            : false;
    }

    function fullJoin(
        $leftTable = '',
        $rightTable = '',
        $leftColumn = null,
        $rightColumn = null,
        $tableAs = null,
        $condition = \EQ
    ) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->fullJoin($leftTable, $rightTable, $leftColumn, $rightColumn, $tableAs, $condition)
            : false;
    }

    function union($table = '', $columnFields = '*', ...$conditions)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->union($table, $columnFields, ...$conditions)
            : false;
    }

    function unionAll($table = '', $columnFields = '*', ...$conditions)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->unionAll($table, $columnFields, ...$conditions)
            : false;
    }

    function orderBy($orderBy, $order)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->orderBy($orderBy, $order)
            : false;
    }

    function limit($numberOf, $offset = null)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->limit($numberOf, $offset)
            : false;
    }

    function insert($table = '', $keyValue = null)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->insert($table, $keyValue)
            : false;
    }

    function update($table = '', $keyValue = null, ...$args)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->update($table, $keyValue, ...$args)
            : false;
    }

    function deleting($table = '', ...$args)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->delete($table, ...$args)
            : false;
    }

    function replace($table = '', $keyValue = null)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->replace($table, $keyValue)
            : false;
    }

    function ezFunctions()
    {
        return true;
    }
}
