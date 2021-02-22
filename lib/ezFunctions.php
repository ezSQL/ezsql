<?php

declare(strict_types=1);

namespace ezsql\functions;

use ezsql\ezQuery;
use ezsql\ezSchema;
use ezsql\Database;
use ezsql\ezQueryInterface;
use ezsql\DatabaseInterface;
use ezsql\ezsqlModelInterface;

if (!\function_exists('ezFunctions')) {
    /**
     * Initialize and connect a vendor's database.
     *
     * @param string $sqlDriver - SQL driver
     * @param array $connectionSetting SQL connection parameters, in the following:
     *```js
     * [
     *  user,  // The database user name.
     *  password, // The database users password.
     *  database, // The name of the database.
     *  host,   // The host name or IP address of the database server. Default is localhost
     *  port    // The  database TCP/IP port. Default is: 5432 - PostgreSQL, 3306 - MySQL
     * ]
     *```
     *  for: **mysqli** - (`username`, `password`, `database`, `host`, `port`, `charset`)
     * - `charset` // The database charset,
     *      Default is empty string
     *
     *  for: **postgresql** - (`username`, `password`, `database`, `host`, `port`)
     *
     *  for: **sqlserver** - (`username`, `password`, `database`, `host`, `convertMysqlToMssqlQuery`)
     * - `convertMysqlToMssqlQuery` // convert Queries in MySql syntax to MS-SQL syntax
     *      Default is false
     *
     *  for: **pdo** - (`dsn`, `username`, `password`, `options`, `isFile`?)
     * - `dsn`  // The PDO DSN connection parameter string
     * - `options` // Array for setting connection options as MySQL
     * - `isFile` // File based databases like SQLite don't need
     *      user and password, they work with path in the dsn parameter
     *      Default is false
     *
     *  for: **sqlite3** - (`filePath`, `database`)
     * - `filePath` // The path to open an SQLite database
     *
     * @param string $instanceTag - Store the instance for later use
     * @return \ezsql\Database\ez_pdo|\ezsql\Database\ez_pgsql|\ezsql\Database\ez_sqlsrv|\ezsql\Database\ez_sqlite3|\ezsql\Database\ez_mysqli
     */
    function database(string $sqlDriver = null, array $connectionSetting = null, string $instanceTag = null)
    {
        return Database::initialize($sqlDriver, $connectionSetting, $instanceTag);
    }

    /**
     * Returns an already initialized database instance that was created with an tag.
     *
     * @param string $getTag - An stored tag instance
     * @return \ezsql\Database\ez_pdo|\ezsql\Database\ez_pgsql|\ezsql\Database\ez_sqlsrv|\ezsql\Database\ez_sqlite3|\ezsql\Database\ez_mysqli
     */
    function tagInstance(string $getTag = null)
    {
        return database($getTag);
    }

    /**
     * Initialize an mysqli database.
     *
     * @param array $databaseSetting SQL connection parameters
     * - [ `username`, `password`, `database`, host, port, charset ]
     * @param string $instanceTag - Store the instance for later use
     *
     * @return \ezsql\Database\ez_mysqli
     */
    function mysqlInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return database(\MYSQLI, $databaseSetting, $instanceTag);
    }

    /**
     * Initialize an pgsql database.
     *
     * @param array $databaseSetting SQL connection parameters
     * - [ `username`, `password`, `database`, host, port ]
     * @param string $instanceTag - Store the instance for later use
     *
     * @return \ezsql\Database\ez_pgsql
     */
    function pgsqlInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return database(\PGSQL, $databaseSetting, $instanceTag);
    }

    /**
     * Initialize an mssql database.
     *
     * @param array $databaseSetting - SQL connection parameters
     * @param string $instanceTag - Store the instance for later use
     *
     * @return \ezsql\Database\ez_sqlsrv
     */
    function mssqlInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return database(\MSSQL, $databaseSetting, $instanceTag);
    }

    /**
     * Initialize an pdo database.
     *
     * @param array $databaseSetting - SQL connection parameters
     * @param string $instanceTag - Store the instance for later use
     *
     * @return \ezsql\Database\ez_pdo
     */
    function pdoInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return database(\Pdo, $databaseSetting, $instanceTag);
    }

    /**
     * Initialize an sqlite3 database.
     *
     * @param array $databaseSetting - SQL connection parameters
     * @param string $instanceTag - Store the instance for later use
     *
     * @return \ezsql\Database\ez_sqlite3
     */
    function sqliteInstance(array $databaseSetting = null, string $instanceTag = null)
    {
        return database(\SQLITE3, $databaseSetting, $instanceTag);
    }

    /**
     * Returns database vendor string, either the global instance, or provided class instance.
     * @param \ezsql\DatabaseInterface|null $instance
     *
     * @return string|null `mysqli`|`pgsql`|`sqlite3`|`sqlsrv`
     */
    function get_vendor(DatabaseInterface $instance = null)
    {
        return ezSchema::vendor($instance);
    }

    /**
     * Convert array to string, and attach '`,`' for separation, if none is provided.
     *
     * @return string
     */
    function to_string($arrays, $separation = ',')
    {
        return ezQuery::to_string($arrays, $separation);
    }

    /**
     * Creates an database column as:
     * - `column`, data`type`, ...value/options `arguments`.
     *
     * // datatype are global `CONSTANTS` and can be written out like:
     *      - VARCHAR, 32, notNULL, PRIMARY, SEQUENCE|AUTO, ....
     * // SEQUENCE|AUTO constants will replaced with the proper auto sequence for the SQL driver
     *
     * @param string $column | `CONSTRAINT`, - column name/CONSTRAINT usage for PRIMARY|FOREIGN KEY
     * @param string $type | constraintName, - data type for column/primary|foreign constraint name
     * @param mixed ...$arguments any remainder assignments `ordered` like:
     *  - @param mixed $size, or/and
     *  - @param mixed $value, - or/and column should be `NULLS`|`notNULL`. If omitted, assumes `NULLS`
     *  - @param mixed $default, - or/and Optional. It is the value to assign to the column
     *  - @param mixed $autoNumber, or/and `AUTO` for vendor's auto numbering
     *  - @param mixed $primaryForeignKeys | or/and `PRIMARY`|`FOREIGN`
     *
     * @return string|bool - SQL schema string, or false for error
     */
    function column(string $column = null, string $type = null, ...$arguments)
    {
        return ezSchema::column($column, $type, ...$arguments);
    }

    function primary(string $primaryName, ...$primaryKeys)
    {
        \array_unshift($primaryKeys, \PRIMARY);
        return column(\CONSTRAINT, $primaryName, ...$primaryKeys);
    }

    function foreign(string $foreignName, ...$foreignKeys)
    {
        \array_unshift($foreignKeys, \FOREIGN);
        return column(\CONSTRAINT, $foreignName, ...$foreignKeys);
    }

    function unique(string $uniqueName, ...$uniqueKeys)
    {
        \array_unshift($uniqueKeys, \UNIQUE);
        return column(\CONSTRAINT, $uniqueName, ...$uniqueKeys);
    }

    function index(string $indexName, ...$indexKeys)
    {
        return column(\INDEX, $indexName, ...$indexKeys);
    }

    function addColumn(string $columnName, ...$datatype)
    {
        return column(\ADD, $columnName, ...$datatype);
    }

    function dropColumn(string $columnName, ...$data)
    {
        return column(\DROP, $columnName, ...$data);
    }

    function changingColumn(string $columnName, ...$datatype)
    {
        return column(\CHANGER, $columnName, ...$datatype);
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
     * Get multiple row result set from the database (previously cached results).
     * Returns a multi dimensional array.
     *
     * Each element of the array contains one row of results and can be
     * specified to be either an `object`, `json`, `associative array` or `numerical
     * array`.
     * - If no results are found then the function returns `false`,
     * enabling you to use the function within logic statements such as if.
     *
     * **OBJECT** - `Returning results as an object` is the quickest way to get and
     * display results. It is also useful that you are able to put
     * `$object->var` syntax directly inside print statements without
     * having to worry about causing php parsing errors.
     *
     * **ARRAY_A** - `Returning results as an associative array` is useful if you would
     * like dynamic access to column names.
     *
     * **ARRAY_N** - `Returning results as a numerical array` is useful if you are using
     * completely dynamic queries with varying column names but still need
     * a way to get a handle on the results.
     *
     * **JSON** - `Returning results as JSON encoded` is useful for any interactive dynamic queries.
     *
     * @param constant $output Either: `OBJECT`|`ARRAY_A`|`ARRAY_N`|`JSON`
     * @param object|null $instance `ez_pdo`|`ez_pgsql`|`ez_sqlsrv`|`ez_sqlite3`|`ez_mysqli`
     * @return bool|object|array - results as objects (default)
     */
    function get_results($output = \OBJECT, $instance = null)
    {
        $ezQuery = empty($instance) || !is_object($instance) ? getInstance() : $instance;
        return ($ezQuery instanceof ezsqlModelInterface)
            ? $ezQuery->get_results(null, $output, false)
            : false;
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
     * Clean input string of XSS, html, javascript, etc...
     * @param string $string
     *
     * @return string cleaned string
     */
    function clean_string(string $string)
    {
        $patterns = array( // strip out:
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si',          // HTML tags
            '@<style[^>]*?>.*?</style>@siU',  // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'       // Strip multi-line comments
        );

        $string = \preg_replace($patterns, '', $string);
        $string = \trim($string);
        $string = \stripslashes($string);

        return \htmlentities($string);
    }

    /**
     * Check if path/filename is directory traversal attack.
     *
     * @param string $basePath base directory to check against
     * @param string $filename will be preprocess with `sanitize_path()`
     * @return boolean
     */
    function is_traversal(string $basePath, string $filename)
    {
        if (\strpos(\urldecode($filename), '..') !== false)
            return true;

        $realBase = \rtrim(\realpath($basePath), _DS);
        $userPath = $realBase . _DS . sanitize_path($filename);
        $realUserPath = \realpath($userPath);
        // Reassign with un-sanitized if file does not exits
        if ($realUserPath === false)
            $realUserPath = $filename;

        return (\strpos($realUserPath, $realBase) !== 0);
    }

    /**
     * Sanitize path to prevent directory traversal.
     *
     * Example:
     *
     *  `sanitize_path("../../../../config.php");`
     *
     *      Returns `config.php` without the path traversal
     * @param string $path
     * @return string
     */
    function sanitize_path(string $path)
    {
        $file = \preg_replace("/\.[\.]+/", "", $path);
        $file = \preg_replace("/^[\/]+/", "", $file);
        $file = \preg_replace("/^[A-Za-z][:\|][\/]?/", "", $file);
        return ($file);
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
    function create_certificate(
        string $privatekeyFile = 'certificate.key',
        string $certificateFile = 'certificate.crt',
        string $signingFile = 'certificate.csr',
        // string $caCertificate = null,
        string $ssl_path = null,
        array $details = ["commonName" => "localhost"]
    ) {
        if (empty($ssl_path)) {
            $ssl_path = \getcwd();
            $ssl_path = \preg_replace('/\\\/', \_DS, $ssl_path) . \_DS;
        } else
            $ssl_path = $ssl_path . \_DS;

        $opensslConfig = array("config" => $ssl_path . 'openssl.cnf');

        // Generate a new private (and public) key pair
        $privatekey = \openssl_pkey_new($opensslConfig);

        // Generate a certificate signing request
        $csr = \openssl_csr_new($details, $privatekey, $opensslConfig);

        // Create a self-signed certificate valid for 365 days
        $sslcert = \openssl_csr_sign($csr, null, $privatekey, 365, $opensslConfig);

        // Create key file. Note no passphrase
        \openssl_pkey_export_to_file($privatekey, $ssl_path . $privatekeyFile, null, $opensslConfig);

        // Create server certificate
        \openssl_x509_export_to_file($sslcert, $ssl_path . $certificateFile, false);

        // Create a signing request file
        \openssl_csr_export_to_file($csr, $ssl_path . $signingFile);

        return $ssl_path;
    }

    /**
     * Preforms a `select` method call on a already preset `table name`, and optional `prefix`
     *
     * This function **expects** either `table_setup(name, prefix)`, `set_table(name)`, or `set_prefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.

     * Returns an `result` set, given the
     * - column fields, conditions or conditional array.
     *
     * In the following format:
     * ```php
     * selecting(
     *   columns,
     *   innerJoin() | leftJoin() | rightJoin() | fullJoin(), // alias of joining(inner|left|right|full, leftTable, rightTable, leftColumn, rightColumn, equal condition),
     *   where( eq( columns, values, _AND ), like( columns, _d ) ),
     *   groupBy( columns ),
     *   having( between( columns, values1, values2 ) ),
     *   orderBy( columns, desc ),
     *   limit( numberOfRecords, offset ),
     *   union(table, columnFields, conditions), // Returns an select SQL string with `UNION`
     *   unionAll(table, columnFields, conditions) // Returns an select SQL string with `UNION ALL`
     *);
     * ```
     *
     * @param mixed $columns fields, string or array
     * @param mixed ...$conditions - of the following parameters:
     *
     * @param $joins, - `joining` clause (type, left table, right table, left column, right column, condition = EQ)
     * - Either: `innerJoin()`, `leftJoin()`, `rightJoin()`, `fullJoin()`
     * - Alias of: `joining(inner|left|right|full, leftTable, rightTable, leftColumn, rightColumn, equal condition)`
     * @param $whereCondition, - `where` clause ( comparison(x, y, and) )
     * @param $groupBy, - `groupBy` clause
     * @param $having, - `having` clause ( comparison(x, y, and) )
     * @param $orderby, - `orderby` clause for the query
     * @param $limit, - `limit` clause the number of records
     * @param $union/$unionAll - `union` clause combine the result sets and removes duplicate rows/does not remove
     *
     * @return mixed|object result set - see docs for more details, or false for error
     */
    function selecting($columns = '*', ...$conditions)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->selecting($columns, ...$conditions)
            : false;
    }

    /**
     * Preforms a `insert` method call on a already preset `table name`, and optional `prefix`
     *
     * This function **expects** either `table_setup(name, prefix)`, `set_table(name)`, or `set_prefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Does an `insert` query with an array
     * @param array $keyValue - table fields, assoc array with key = value (doesn't need escaping)
     * @return int|bool bool/id of inserted record, or false for error
     */
    function inserting(array $keyValue)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->inserting($keyValue)
            : false;
    }

    /**
     * Preforms a `update` method call on a already preset `table name`, and optional `prefix`
     *
     * This function **expects** either `table_setup(name, prefix)`, `set_table(name)`, or `set_prefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Does an `update` query with an array, by conditional operator array
     * @param array $keyValue, - table fields, assoc array with key = value (doesn't need escaped)
     * @param mixed ...$whereConditions, - where clause `eq(x, y, _AND), another clause - same as array(x, =, y, and, extra)`
     * - In the following format:
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
     * @return mixed bool/results - false for error
     */
    function updating(array $keyValue, ...$whereConditions)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->updating($keyValue, ...$whereConditions)
            : false;
    }

    /**
     * Preforms a `create` method call on a already preset `table name`, and optional `prefix`
     *
     * This function **expects** either `table_setup(name, prefix)`, `set_table(name)`, or `set_prefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Creates an database table with columns, by either:
     *```js
     *  - array( column, datatype, ...value/options arguments ) // calls create_schema()
     *  - column( column, datatype, ...value/options arguments ) // returns string
     *  - primary( primary_key_label, ...primaryKeys) // returns string
     *  - foreign( foreign_key_label, ...foreignKeys) // returns string
     *  - unique( unique_key_label, ...uniqueKeys) // returns string
     *```
     * @param array ...$schemas An array of:
     *
     * - @param string `$column | CONSTRAINT,` - column name/CONSTRAINT usage for PRIMARY|FOREIGN KEY
     * - @param string `$type | $constraintName,` - data type for column/primary | foreign constraint name
     * - @param mixed `$size | ...$primaryForeignKeys,`
     * - @param mixed `$value,` - column should be NULL or NOT NULL. If omitted, assumes NULL
     * - @param mixed `$default` - Optional. It is the value to assign to the column
     *
     * @return mixed results of query() call
     */
    function creating(...$schemas)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->creating(...$schemas)
            : false;
    }

    /**
     * Preforms a `delete` method call on a already preset `table name`, and optional `prefix`
     *
     * This function **expects** either `table_setup(name, prefix)`, `set_table(name)`, or `set_prefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Does an `delete` query with an array
     * @param $table, - database table to access
     * @param $whereConditions, - where clause `eq(x, y, _AND), another clause - same as array(x, =, y, and, extra)`
     * - In the following format:
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
     * @return mixed bool/results - false for error
     */
    function deleting(...$whereConditions)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->deleting(...$whereConditions)
            : false;
    }

    /**
     * Preforms a `replace` method call on a already preset `table name`, and optional `prefix`
     *
     * This function **expects** either `table_setup(name, prefix)`, `set_table(name)`, or `set_prefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Does an `replace` query with an array
     * @param array $keyValue - table fields, assoc array with key = value (doesn't need escaping)
     * @return mixed bool/id of replaced record, or false for error
     */
    function replacing(array $keyValue)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->replacing($keyValue)
            : false;
    }

    /**
     * Preforms a `drop` method call on a already preset `table name`, and optional `prefix`
     *
     * This function **expects** either `table_setup(name, prefix)`, `set_table(name)`, or `set_prefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Does an `drop` table query if table exists.
     *
     * @return bool|int
     */
    function dropping()
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->dropping()
            : false;
    }

    /**
     * Preforms a `alter` method call on a already preset `table name`, and optional `prefix`
     *
     * This method **expects** either `table_setup(name, prefix)`, `set_table(name)`, or `set_prefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Modify columns in an existing database table, by either:
     *```js
     *  - array( column_name, datatype, ...value/options arguments ) // calls create_schema()
     *  - addColumn( column_name, datatype, ...value/options arguments ) // returns string
     *  - dropColumn( column_name ) // returns string
     *  - changingColumn( column_name, datatype, ...value/options arguments ) // returns string
     *```
     * @param array ...$alteringSchema An array of:
     *
     * - @param string `$name,` - column name
     * - @param string `$type,` - data type for the column
     * - @param mixed `$size,` | `$value,`
     * - @param mixed `...$anyOtherArgs`
     *
     * @return mixed results of query() call
     */
    function altering(...$alteringSchema)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->altering(...$alteringSchema)
            : false;
    }

    /**
     * Set table `name` and `prefix` for usage on calls to database `CRUD`
     * **method/function** *names* ending with `ing`.
     *
     * @param string $name
     * @param string $prefix
     */
    function table_setup(string $name = '', string $prefix = '')
    {
        $ezQuery = getInstance();
        if (!$ezQuery instanceof ezsqlModelInterface)
            return false;

        $ezQuery->tableSetup($name, $prefix);
    }

    /**
     * Set table `name` to use on calls to database `CRUD` **method/function** *names* ending with `ing`.
     *
     * @param string $name
     */
    function set_table(string $name = '')
    {
        $ezQuery = getInstance();
        if (!$ezQuery instanceof ezsqlModelInterface)
            return false;

        $ezQuery->setTable($name);
    }

    /**
     * Add a `prefix` to **append** to `table` name on calls to database `CRUD`
     * **method/function** *names* ending with `ing`.
     *
     * @param string $append
     */
    function set_prefix(string $append = '')
    {
        $ezQuery = getInstance();
        if (!$ezQuery instanceof ezsqlModelInterface)
            return false;

        $ezQuery->setPrefix($append);
    }

    /**
     * Does an `select into` statement by calling `select` method
     * @param $newTable, - new database table to be created
     * @param $fromColumns - the columns from old database table
     * @param $oldTable - old database table
     * @param $fromWhereConditions, - where clause `eq(x, y, _AND), another clause - same as array(x, =, y, and, extra) `
     *
     * @return mixed|object bool/result - false for error
     */
    function select_into($newTable, $fromColumns = '*', $oldTable = null, ...$fromWhereConditions)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->select_into($newTable, $fromColumns, $oldTable, ...$fromWhereConditions)
            : false;
    }

    /**
     * Does an `insert into select` statement by calling insert method helper then `select` method
     * @param $toTable, - database table to insert table into
     * @param $toColumns - the receiving columns from other table columns, leave blank for all or array of column fields
     * @param $fromTable, - from database table to use
     * @param $fromColumns - the columns from old database table
     * @param $whereConditions, - where clause `eq(x, y, _AND), another clause - same as array(x, =, y, and, extra)`
     *
     * @return mixed bool/id of inserted record, or false for error
     */
    function insert_select($totable = '', $toColumns = '*', $fromTable = null, $fromColumns = '*', ...$whereConditions)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->insert_select($totable, $toColumns, $fromTable, $fromColumns, ...$whereConditions)
            : false;
    }

    /**
     * Does an `create select` statement by calling `select` method
     *
     * @param $newTable, - new database table to be created
     * @param $fromColumns - the columns from old database table
     * @param $oldTable - old database table
     * @param $fromWhereConditions, - where clause `eq(x, y, _AND), another clause - same as array(x, =, y, and, extra)`
     *
     * @return mixed bool/result - false for error
     */
    function create_select($newTable, $fromColumns = '*', $oldTable = null, ...$fromWhereConditions)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->create_select($newTable, $fromColumns, $oldTable, ...$fromWhereConditions)
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
     * @param array $conditions - In the following format:
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
    function where(...$conditions)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->where(...$conditions)
            : false;
    }

    /**
     * Adds WHERE `grouping` to the conditions
     *
     * format:
     *   `grouping( comparison(x, y, and) )`
     *
     * example:
     *   `grouping( eq(key, value, combiner ), eq(key, value, combiner ) );`
     *
     * @param array $conditions - In the following format:
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
    function grouping(...$conditions)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->grouping(...$conditions)
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
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->groupBy($groupBy)
            : false;
    }

    /**
     * Specifies a `restriction` over the groups of the query.
     *
     * format
     *   `having( array(x, =, y, and, extra) );` or
     *   `having( "x  =  y  and  extra" );`
     *
     * example:
     *   `having( array(key, operator, value, combine, extra) );`or
     *   `having( "key operator value combine extra" );`
     *
     * @param array $conditions - In the following format:
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
     * @return bool/string - HAVING SQL statement, or false on error
     */
    function having(...$conditions)
    {
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->having(...$conditions)
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
        $ezQuery = getInstance();
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
        $ezQuery = getInstance();
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
        $ezQuery = getInstance();
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
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->fullJoin($leftTable, $rightTable, $leftColumn, $rightColumn, $tableAs, $condition)
            : false;
    }

    /**
     * Returns an `UNION` SELECT `SQL` string, given the
     *   - table, column fields, conditions or conditional array.
     *
     * In the following format:
     * ```php
     * union(
     *   table,
     *   columns,
     *   innerJoin() | leftJoin() | rightJoin() | fullJoin(), // alias of joining(inner|left|right|full, leftTable, rightTable, leftColumn, rightColumn, equal condition),
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
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->union($table, $columnFields, ...$conditions)
            : false;
    }

    /**
     * Returns an `UNION ALL` SELECT `SQL` string, given the
     *   - table, column fields, conditions or conditional array.
     *
     * In the following format:
     * ```php
     * unionAll(
     *   table,
     *   columns,
     *   innerJoin() | leftJoin() | rightJoin() | fullJoin(), // alias of joining(inner|left|right|full, leftTable, rightTable, leftColumn, rightColumn, equal condition),
     *   where( eq( columns, values, _AND ), like( columns, _d ) ),
     *   groupBy( columns ),
     *   having( between( columns, values1, values2 ) ),
     *   orderBy( columns, desc ),
     *   limit( numberOfRecords, offset )
     *);
     * ```
     * @param $table, - database table to access
     * @param $columnFields, - table columns, string or array
     * @param mixed $conditions - same as `select` method.
     *
     * @return bool|string - false for error
     */
    function unionAll($table = '', $columnFields = '*', ...$conditions)
    {
        $ezQuery = getInstance();
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
        $ezQuery = getInstance();
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
        $ezQuery = getInstance();
        return ($ezQuery instanceof DatabaseInterface)
            ? $ezQuery->limit($numberOf, $offset)
            : false;
    }

    function ezFunctions()
    {
        return true;
    }
}
