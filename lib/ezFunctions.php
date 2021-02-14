<?php

use ezsql\ezQuery;
use ezsql\ezSchema;
use ezsql\Database;
use ezsql\ezQueryInterface;
use ezsql\DatabaseInterface;

if (!\function_exists('ezFunctions')) {
    /**
     * Initialize and connect a vendor database.
     *
     * @param mixed $sqlDriver - SQL driver
     * @param mixed $connectionSetting - SQL connection parameters
     * @param mixed $instanceTag - Store the instance for later use
     * @return ezsql\Database\ez_pdo|ezsql\Database\ez_pgsql|ezsql\Database\ez_sqlsrv|Database\ez_sqlite3|ezsql\Database\ez_mysqli
     */
    function database(string $sqlDriver = null, array $connectionSetting = null, string $instanceTag = null)
    {
        return Database::initialize($sqlDriver, $connectionSetting, $instanceTag);
    }

    /**
     * Returns an already initialized database instance that was created an tag.
     *
     * @param string $getTag - An stored tag instance
     * @return ezsql\Database\ez_pdo|ezsql\Database\ez_pgsql|ezsql\Database\ez_sqlsrv|Database\ez_sqlite3|ezsql\Database\ez_mysqli
     */
    function tagInstance(string $getTag = null)
    {
        return \database($getTag);
    }

    /**
     * Initialize an mysqli database.
     *
     * @param array $databaseSetting - SQL connection parameters
     * @param mixed $instanceTag - Store the instance for later use
     *
     * @return ezsql\Database\ez_mysqli
     */
    function mysqlInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return \database(\MYSQLI, $databaseSetting, $instanceTag);
    }

    /**
     * Initialize an pgsql database.
     *
     * @param mixed $databaseSetting - SQL connection parameters
     * @param mixed $instanceTag - Store the instance for later use
     *
     * @return ezsql\Database\ez_pgsql
     */
    function pgsqlInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return \database(\PGSQL, $databaseSetting, $instanceTag);
    }

    /**
     * Initialize an mssql database.
     *
     * @param mixed $databaseSetting - SQL connection parameters
     * @param mixed $instanceTag - Store the instance for later use
     *
     * @return ezsql\Database\ez_sqlsrv
     */
    function mssqlInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return \database(\MSSQL, $databaseSetting, $instanceTag);
    }

    /**
     * Initialize an pdo database.
     *
     * @param mixed $databaseSetting - SQL connection parameters
     * @param mixed $instanceTag - Store the instance for later use
     *
     * @return ezsql\Database\ez_pdo
     */
    function pdoInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return \database(\Pdo, $databaseSetting, $instanceTag);
    }

    /**
     * Initialize an sqlite3 database.
     *
     * @param mixed $databaseSetting - SQL connection parameters
     * @param mixed $instanceTag - Store the instance for later use
     *
     * @return ezsql\Database\ez_sqlite3
     */
    function sqliteInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return \database(\SQLITE3, $databaseSetting, $instanceTag);
    }

    /**
     * Returns the current global database vendor being used.
     *
     * @return string|null `mysqli`|`pgsql`|`sqlite3`|`sqlsrv`
     */
    function getVendor()
    {
        return ezSchema::vendor();
    }

    /**
     * Convert array to string, and attach '`, `' for separation, if none is provided.
     *
     * @return string
     */
    function to_string($arrays, $separation = ',')
    {
        return ezQuery::to_string($arrays, $separation);
    }

    /**
     * Creates an database column,
     * - column, datatype, value/options with the given arguments.
     *
     * // datatype are global CONSTANTS and can be written out like:
     *      - VARCHAR, 32, notNULL, PRIMARY, SEQUENCE|AUTO, ....
     * // SEQUENCE|AUTO constants will replaced with the proper auto sequence for the SQL driver
     *
     * @param string $column|CONSTRAINT, - column name/CONSTRAINT usage for PRIMARY|FOREIGN KEY
     * @param string $type|$constraintName, - data type for column/primary|foreign constraint name
     * @param mixed $size|...$primaryForeignKeys,
     * @param mixed $value, - column should be NULL or NOT NULL. If omitted, assumes NULL
     * @param mixed $default - Optional. It is the value to assign to the column
     *
     * @return string|bool - SQL schema string, or false for error
     */
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

    /**
     * Creates self signed certificate
     *
     * @param string $privatekeyFile
     * @param string $certificateFile
     * @param string $signingFile
     * // param string $caCertificate
     * @param string $ssl_path
     * @param array $details - certificate details
     *
     * Example:
     *  array $details = [
     *      "countryName" =>  '',
     *      "stateOrProvinceName" => '',
     *      "localityName" => '',
     *      "organizationName" => '',
     *      "organizationalUnitName" => '',
     *      "commonName" => '',
     *      "emailAddress" => ''
     *  ];
     *
     * @return string certificate path
     */
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
     * Creates an equality comparison expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function eq($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \EQ, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a non equality comparison expression with the given arguments.
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
    function neq($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \NEQ, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates the other non equality comparison expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function ne($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \NE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a lower-than comparison expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function lt($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \LT, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a lower-than-equal comparison expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function lte($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \LTE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a greater-than comparison expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function gt($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \GT, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a greater-than-equal comparison expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function gte($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \GTE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates an IS NULL expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function isNull($x, $y = 'null', $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_isNULL, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates an IS NOT NULL expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function isNotNull($x, $y = 'null', $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_notNULL, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a LIKE() comparison expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function like($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_LIKE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT LIKE() comparison expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function notLike($x, $y, $and = null, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_notLIKE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a IN () comparison expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function in($x, $y, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_IN, $y, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT IN () comparison expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function notIn($x, $y, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_notIN, $y, ...$args);
        return $expression;
    }

    /**
     * Creates a BETWEEN () comparison expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function between($x, $y, $y2, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_BETWEEN, $y, $y2, \_AND, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT BETWEEN () comparison expression with the given arguments.
     *
     * @param strings $x, - The left expression.
     * @param strings $y, - The right expression.
     * @param strings $and, - combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     * @param strings $args - for any extras
     *
     * @return array
     */
    function notBetween($x, $y, $y2, ...$args)
    {
        $expression = array();
        \array_push($expression, $x, \_notBETWEEN, $y, $y2, \_AND, ...$args);
        return $expression;
    }

    /**
     * Sets the global class instance for functions to call class methods directly.
     *
     * @param ezQueryInterface|null $ezSQL
     *
     * @return boolean - `true`, or `false` for error
     */
    function setInstance(ezQueryInterface $ezSQL = null)
    {
        global $ezInstance;
        $status = false;

        if ($ezSQL instanceof ezQueryInterface) {
            $ezInstance = $ezSQL;
            $status = true;
        }

        return $status;
    }

    /**
     * Returns the global database class, last created instance or the one set with `setInstance()`.
     *
     * @return ezQueryInterface|null
     */
    function getInstance()
    {
        global $ezInstance;

        return ($ezInstance instanceof ezQueryInterface) ? $ezInstance : null;
    }

    /**
     * Clear/unset the global database class instance.
     */
    function clearInstance()
    {
        global $ezInstance;
        $GLOBALS['ezInstance'] = null;
        $ezInstance = null;
        unset($GLOBALS['ezInstance']);
    }

    /**
     * Clean input of XSS, html, javascript, etc...
     * @param string $string
     *
     * @return string cleaned string
     */
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
     * @param $joins, - join clause (type, left table, right table, left column, right column, condition = EQ)
     * @param $whereKey, - where clause ( comparison(x, y, and) )
     * @param $groupBy, - grouping over clause the results
     * @param $having, - having clause ( comparison(x, y, and) )
     * @param $orderby, - ordering by clause for the query
     * @param $limit, - limit clause the number of records
     * @param $union/$unionAll - union clause combine the result sets and removes duplicate rows/does not remove
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

    /**
     * Does an insert into select statement by calling insert method helper then selecting method
     * @param $toTable, - database table to insert table into
     * @param $toColumns - the receiving columns from other table columns, leave blank for all or array of column fields
     * @param $WhereKey, - where clause ( array(x, =, y, and, extra) ) or ( "x = y and extra" )
     *
     * @return mixed bool/id of inserted record, or false for error
     */
    function insert_select($totable = '', $columns = '*', $fromTable = null, $from = '*', ...$args)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->insert_select($totable, $columns, $fromTable, $from, ...$args)
            : false;
    }

    /**
     * Does an create select statement by calling selecting method
     *
     * @param $newTable, - new database table to be created
     * @param $fromColumns - the columns from old database table
     * @param $oldTable - old database table
     * @param $fromWhere, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
     *
     * @return mixed bool/result - false for error
     */
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

    /**
     * Specifies a grouping over the results of the query.
     *<code>
     *   selecting('table',
     *       'columns',
     *        where( eq( 'columns', values, _AND ), like( 'columns', _d ) ),
     *        groupBy( 'columns' ),
     *        having( between( 'columns', values1, values2 ) ),
     *        orderBy( 'columns', 'desc' );
     *</code>
     * @param mixed $groupBy The grouping expression.
     *
     * @return string - GROUP BY SQL statement, or false on error
     */
    function groupBy($groupBy)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->groupBy($groupBy)
            : false;
    }

    /**
     * Specifies a restriction over the groups of the query.
     *
     * format
     *   `having( array(x, =, y, and, extra) );` or
     *   `having( "x  =  y  and  extra" );`
     *
     * example:
     *   `having( array(key, operator, value, combine, extra) );`or
     *   `having( "key operator value combine extra" );`
     *
     * @param array $having
     * @param string $key, - table column
     * @param string $operator, - set the operator condition,
     *                       either '<','>', '=', '!=', '>=', '<=', '<>', 'in',
     *                           'like', 'between', 'not between', 'is null', 'is not null'
     * @param mixed $value, - will be escaped
     * @param string $combine, - combine additional where clauses with,
     *                       either 'AND','OR', 'NOT', 'AND NOT'
     *                           or  carry over of @value in the case the @operator is 'between' or 'not between'
     * @param string $extra - carry over of @combine in the case the operator is 'between' or 'not between'
     * @return bool/string - HAVING SQL statement, or false on error
     */
    function having(...$args)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->having(...$args)
            : false;
    }

    /**
     * Return all rows from multiple tables where the join condition is met.
     *
     * - Will perform an equal on tables by left column key,
     *       left column key and left table, left column key and right table,
     *           if `rightColumn` is null.
     *
     * - Will perform an equal on tables by,
     *       left column key and left table, right column key and right table,
     *           if `rightColumn` not null, and `$condition` not changed.
     *
     * - Will perform the `condition` on passed in arguments, for left column, and right column.
     *           if `$condition`,  is in the array
     *
     * @param string $leftTable -
     * @param string $rightTable -
     * @param string $leftColumn -
     * @param string $rightColumn -
     * @param string $tableAs -
     * @param string $condition -
     *
     * @return bool|string JOIN sql statement, false for error
     */
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

    /**
     * This type of join returns all rows from the LEFT-hand table
     * specified in the ON condition and only those rows from the other table
     * where the joined fields are equal (join condition is met).
     *
     * - Will perform an equal on tables by left column key,
     *       left column key and left table, left column key and right table,
     *           if `rightColumn` is null.
     *
     * - Will perform an equal on tables by,
     *       left column key and left table, right column key and right table,
     *           if `rightColumn` not null, and `$condition` not changed.
     *
     * - Will perform the `condition` on passed in arguments, for left column, and right column.
     *           if `$condition`,  is in the array
     *
     * @param string $leftTable -
     * @param string $rightTable -
     * @param string $leftColumn -
     * @param string $rightColumn -
     * @param string $tableAs -
     * @param string $condition -
     *
     * @return bool|string JOIN sql statement, false for error
     */
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

    /**
     * This type of join returns all rows from the RIGHT-hand table
     * specified in the ON condition and only those rows from the other table
     * where the joined fields are equal (join condition is met).
     *
     * - Will perform an equal on tables by left column key,
     *       left column key and left table, left column key and right table,
     *           if `rightColumn` is null.
     *
     * - Will perform an equal on tables by,
     *       left column key and left table, right column key and right table,
     *           if `rightColumn` not null, and `$condition` not changed.
     *
     * - Will perform the `condition` on passed in arguments, for left column, and right column.
     *           if `$condition`,  is in the array
     *
     * @param string $leftTable -
     * @param string $rightTable -
     * @param string $leftColumn -
     * @param string $rightColumn -
     * @param string $tableAs -
     * @param string $condition -
     *
     * @return bool|string JOIN sql statement, false for error
     */
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

    /**
     * This type of join returns all rows from the LEFT-hand table and RIGHT-hand table
     * with NULL values in place where the join condition is not met.
     *
     * - Will perform an equal on tables by left column key,
     *       left column key and left table, left column key and right table,
     *           if `rightColumn` is null.
     *
     * - Will perform an equal on tables by,
     *       left column key and left table, right column key and right table,
     *           if `rightColumn` not null, and `$condition` not changed.
     *
     * - Will perform the `condition` on passed in arguments, for left column, and right column.
     *           if `$condition`,  is in the array
     *
     * @param string $leftTable -
     * @param string $rightTable -
     * @param string $leftColumn -
     * @param string $rightColumn -
     * @param string $tableAs -
     * @param string $condition -
     *
     * @return bool|string JOIN sql statement, false for error
     */
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

    /**
     * Returns an `UNION` SELECT SQL string, given the
     *   - table, column fields, conditions or conditional array.
     *
     * In the following format:
     * ```
     * union(
     *   table,
     *   columns,
     *   // innerJoin(), leftJoin(), rightJoin(), fullJoin() alias of
     *   joining(inner|left|right|full, leftTable, rightTable, leftColumn, rightColumn, equal condition),
     *   where( eq( columns, values, _AND ), like( columns, _d ) ),
     *   groupBy( columns ),
     *   having( between( columns, values1, values2 ) ),
     *   orderBy( columns, desc ),
     *   limit( numberOfRecords, offset )
     *);
     * ```
     * @param $table, - database table to access
     * @param $columnFields, - table columns, string or array
     * @param mixed $conditions - same as selecting method.
     *
     * @return bool|string - false for error
     */
    function union($table = '', $columnFields = '*', ...$conditions)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->union($table, $columnFields, ...$conditions)
            : false;
    }

    /**
     * Returns an `UNION ALL` SELECT SQL string, given the
     *   - table, column fields, conditions or conditional array.
     *
     * In the following format:
     * ```
     * unionAll(
     *   table,
     *   columns,
     *   // innerJoin(), leftJoin(), rightJoin(), fullJoin() alias of
     *   joining(inner|left|right|full, leftTable, rightTable, leftColumn, rightColumn, equal condition),
     *   where( eq( columns, values, _AND ), like( columns, _d ) ),
     *   groupBy( columns ),
     *   having( between( columns, values1, values2 ) ),
     *   orderBy( columns, desc ),
     *   limit( numberOfRecords, offset )
     *);
     * ```
     * @param $table, - database table to access
     * @param $columnFields, - table columns, string or array
     * @param mixed $conditions - same as selecting method.
     *
     * @return bool|string - false for error
     */
    function unionAll($table = '', $columnFields = '*', ...$conditions)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->unionAll($table, $columnFields, ...$conditions)
            : false;
    }

    /**
     * Specifies an ordering for the query results.
     * @param string $orderBy - The column.
     * @param string $order - The ordering direction.
     *
     * @return string - ORDER BY SQL statement, or false on error
     */
    function orderBy($orderBy, $order)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->orderBy($orderBy, $order)
            : false;
    }

    /**
     * Specifies records from one or more tables in a database and
     * limit the number of records returned.
     *
     * @param int $numberOf - set limit number of records to be returned.
     * @param int $offset - Optional. The first row returned by LIMIT will be determined by offset value.
     *
     * @return string - LIMIT and/or OFFSET SQL statement, or false on error
     */
    function limit($numberOf, $offset = null)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->limit($numberOf, $offset)
            : false;
    }

    /**
     * Does an insert query with an array
     * @param $table, - database table to access
     * @param $keyAndValue - table fields, assoc array with key = value (doesn't need escaped)
     * @return mixed bool/id of inserted record, or false for error
     */
    function insert($table = '', $keyValue = null)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->insert($table, $keyValue)
            : false;
    }

    /**
     * Does an update query with an array, by conditional operator array
     * @param $table, - database table to access
     * @param $keyAndValue, - table fields, assoc array with key = value (doesn't need escaped)
     * @param $WhereKey, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
     *
     * @return mixed bool/results - false for error
     */
    function update($table = '', $keyValue = null, ...$args)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->update($table, $keyValue, ...$args)
            : false;
    }

    /**
     * Does the delete query with an array
     * @return mixed bool/results - false for error
     */
    function deleting($table = '', ...$args)
    {
        $ezQuery = \getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->delete($table, ...$args)
            : false;
    }

    /**
     * Does an replace query with an array
     * @param $table, - database table to access
     * @param $keyAndValue - table fields, assoc array with key = value (doesn't need escaped)
     * @return mixed bool/id of replaced record, or false for error
     */
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
