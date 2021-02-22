<?php

namespace ezsql;

/**
 * Author:  Lawrence Stubbs <technoexpressnet@gmail.com>
 *
 * Important: Verify that every feature you use will work with your database vendor.
 * ezSQL Query Builder will attempt to validate the generated SQL according to standards.
 * Any errors will return an boolean false, and you will be responsible for handling.
 *
 * ezQuery does no validation whatsoever if certain features even work with the
 * underlying database vendor.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the **MIT** license.
 */
interface ezQueryInterface
{
    /**
     * Turn on prepare function availability in ezQuery shortcut method calls
     */
    public function prepareOn();

    /**
     * Turn off prepare function availability in ezQuery shortcut method calls
     */
    public function prepareOff();


    /**
     * Specifies a grouping over the results of the query.
     *<code>
     *   select('table',
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
    public function groupBy($groupBy);

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
     * @param array $conditions
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
     * @return bool/string - HAVING SQL statement, or false on error
     */
    public function having(...$conditions);

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
    public function innerJoin(
        string $leftTable = null,
        string $rightTable = null,
        string $leftColumn = null,
        string $rightColumn = null,
        string $tableAs = null,
        $condition = \EQ
    );

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
    public function leftJoin(
        string $leftTable = null,
        string $rightTable = null,
        string $leftColumn = null,
        string $rightColumn = null,
        string $tableAs = null,
        $condition = \EQ
    );

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
    public function rightJoin(
        string $leftTable = null,
        string $rightTable = null,
        string $leftColumn = null,
        string $rightColumn = null,
        string $tableAs = null,
        $condition = \EQ
    );

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
    public function fullJoin(
        string $leftTable = null,
        string $rightTable = null,
        string $leftColumn = null,
        string $rightColumn = null,
        string $tableAs = null,
        $condition = \EQ
    );

    /**
     * Returns an `UNION` SELECT SQL string, given the
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
     * @param mixed $conditions - same as select method.
     *
     * @return bool|string - false for error
     */
    public function union(string $table = null, $columnFields = '*', ...$conditions);

    /**
     * Returns an `UNION ALL` SELECT SQL string, given the
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
     * @param mixed $conditions - same as select method.
     *
     * @return bool|string - false for error
     */
    public function unionAll(string $table = null, $columnFields = '*', ...$conditions);

    /**
     * Specifies an ordering for the query results.
     * @param string $orderBy - The column.
     * @param string $order - The ordering direction.
     *
     * @return string - ORDER BY SQL statement, or false on error
     */
    public function orderBy($orderBy, $order);

    /**
     * Specifies records from one or more tables in a database and
     * limit the number of records returned.
     *
     * @param int $numberOf - set limit number of records to be returned.
     * @param int $offset - Optional. The first row returned by LIMIT will be determined by offset value.
     *
     * @return string - LIMIT and/or OFFSET SQL statement, or false on error
     */
    public function limit($numberOf, $offset = null);

    /**
     * Adds WHERE `grouping` to the conditions
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
    public function grouping(...$whereConditions);

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
     * @return mixed bool/string - WHERE SQL statement, or false on error
     */
    public function where(...$conditions);


    /**
     * Returns an `SQL string` or `result` set, given the
     *   - database table, column fields, conditions or conditional array.
     *
     * In the following format:
     * ```php
     * select(
     *   table,
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
     * @param $table, - database table to access
     * @param $columnFields, - table columns, string or array
     * @param mixed ...$conditions - of the following parameters:
     *
     * @param $joins, - `joining` clause (type, left table, right table, left column, right column, condition = EQ)
     * - Either: `innerJoin()`, `leftJoin()`, `rightJoin()`, `fullJoin()`
     * - Alias of: `joining(inner|left|right|full, leftTable, rightTable, leftColumn, rightColumn, equal condition)`
     * @param $whereConditions, - `where` clause ( comparison(x, y, and) )
     * @param $groupBy, - `groupBy` clause
     * @param $having, - `having` clause ( comparison(x, y, and) )
     * @param $orderby, - `orderby` clause for the query
     * @param $limit, - `limit` clause the number of records
     * @param $union/$unionAll - `union` clause combine the result sets and removes duplicate rows/does not remove
     *
     * @return mixed|object result set - see docs for more details, or false for error
     */
    public function select(string $table = null, $columnFields = '*', ...$conditions);

    /**
     * Preforms a `select` method call on a already preset `table name`, and optional `prefix`
     *
     * This method **expects** either `tableSetup(name, prefix)`, `setTable(name)`, or `setPrefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Returns an `SQL string` or `result` set, given the
     *   - column fields, conditions or conditional array.
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
     * @param mixed $columns fields, string or array
     * @param mixed ...$conditions - of the following parameters:
     *
     * @param $joins, - `joining` clause (type, left table, right table, left column, right column, condition = EQ)
     * - Either: `innerJoin()`, `leftJoin()`, `rightJoin()`, `fullJoin()`
     * - Alias of: `joining(inner|left|right|full, leftTable, rightTable, leftColumn, rightColumn, equal condition)`
     * @param $whereConditions, - `where` clause ( comparison(x, y, and) )
     * @param $groupBy, - `groupBy` clause
     * @param $having, - `having` clause ( comparison(x, y, and) )
     * @param $orderby, - `orderby` clause for the query
     * @param $limit, - `limit` clause the number of records
     * @param $union/$unionAll - `union` clause combine the result sets and removes duplicate rows/does not remove
     *
     * @return mixed|object result set - see docs for more details, or false for error
     */
    public function selecting($columns = '*', ...$conditions);

    /**
     * Does an `insert` query with an array
     * @param $table, - database table to access
     * @param $keyValue - table fields, assoc array with key = value (doesn't need escaped)
     * @return mixed bool/id of inserted record, or false for error
     */
    public function insert(string $table = null, $keyValue);

    /**
     * Preforms a `insert` method call on a already preset `table name`, and optional `prefix`
     *
     * This method **expects** either `tableSetup(name, prefix)`, `setTable(name)`, or `setPrefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Does an `insert` query with an array
     * @param array $keyValue - table fields, assoc array with key = value (doesn't need escaped)
     * @return int|bool bool/id of inserted record, or false for error
     */
    function inserting(array $keyValue);

    /**
     * Does an `create select` statement by calling `select` method
     *
     * @param $newTable, - new database table to be created
     * @param $fromColumns - the columns from old database table
     * @param $oldTable - old database table
     * @param $fromWhereConditions, - where clause `eq(x, y, _AND), another clause - same as array(x, =, y, and, extra)`
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
     * @return mixed bool/result - false for error
     */
    public function create_select(string $newTable, $fromColumns = '*', $oldTable = null, ...$fromWhereConditions);

    /**
     * Does an `select into` statement by calling `select` method
     * @param $newTable, - new database table to be created
     * @param $fromColumns - the columns from old database table
     * @param $oldTable - old database table
     * @param $fromWhereConditions, - where clause `eq(x, y, _AND), another clause - same as array(x, =, y, and, extra)`
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
     * @return mixed bool/result - false for error
     */
    public function select_into(string $newTable, $fromColumns = '*', $oldTable = null, ...$fromWhereConditions);

    /**
     * Does an `update` query with an array, by conditional operator array
     * @param $table, - database table to access
     * @param $keyValue, - table fields, assoc array with key = value (doesn't need escaped)
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
    public function update(string $table = null, $keyValue, ...$whereConditions);

    /**
     * Preforms a `update` method call on a already preset `table name`, and optional `prefix`
     *
     * This method **expects** either `tableSetup(name, prefix)`, `setTable(name)`, or `setPrefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Does an `update` query with an array, by conditional operator array
     * @param $keyValue, - table fields, assoc array with key = value (doesn't need escaped)
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
    public function updating(array $keyValue, ...$whereConditions);

    /**
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
    public function delete(string $table = null, ...$whereConditions);

    /**
     * Preforms a `delete` method call on a already preset `table name`, and optional `prefix`
     *
     * This method **expects** either `tableSetup(name, prefix)`, `setTable(name)`, or `setPrefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Does an `delete` query with an array
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
    public function deleting(...$whereConditions);

    /**
     * Does an `replace` query with an array
     * @param $table, - database table to access
     * @param $keyValue - table fields, assoc array with key = value (doesn't need escaped)
     * @return mixed bool/id of replaced record, or false for error
     */
    public function replace(string $table = null, $keyValue);

    /**
     * Preforms a `replace` method call on a already preset `table name`, and optional `prefix`
     *
     * This method **expects** either `tableSetup(name, prefix)`, `setTable(name)`, or `setPrefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Does an `replace` query with an array
     * @param array $keyValue - table fields, assoc array with key = value (doesn't need escaping)
     * @return mixed bool/id of replaced record, or false for error
     */
    function replacing(array $keyValue);

    /**
     * Does an `insert into select` statement by calling insert method helper then `select` method
     * @param $toTable, - database table to insert table into
     * @param $toColumns - the receiving columns from other table columns, leave blank for all or array of column fields
     * @param $fromTable, - from database table to use
     * @param $fromColumns - the columns from old database table
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
     * @return mixed bool/id of inserted record, or false for error
     */
    public function insert_select(
        string $toTable = null,
        $toColumns = '*',
        $fromTable = null,
        $fromColumns = '*',
        ...$whereConditions
    );

    /**
     * Creates an database table with columns, by either:
     *```js
     *  - array( column, datatype, ...value/options arguments ) // calls create_schema()
     *  - column( column, datatype, ...value/options arguments ) // returns string
     *  - primary( primary_key_label, ...primaryKeys) // returns string
     *  - foreign( foreign_key_label, ...foreignKeys) // returns string
     *  - unique( unique_key_label, ...uniqueKeys) // returns string
     *```
     * @param string $table, - The name of the db table that you wish to create
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
    public function create(string $table = null, ...$schemas);

    /**
     * Preforms a `create` method call on a already preset `table name`, and optional `prefix`
     *
     * This method **expects** either `tableSetup(name, prefix)`, `setTable(name)`, or `setPrefix(append)`
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
    public function creating(...$schemas);

    /**
     * Does an `drop` table query if table exists.
     * @param string $table - database table to erase
     *
     * @return bool|int
     */
    public function drop(string $table = null);

    /**
     * Preforms a `drop` method call on a already preset `table name`, and optional `prefix`
     *
     * This method **expects** either `tableSetup(name, prefix)`, `setTable(name)`, or `setPrefix(append)`
     * to have been called **before usage**, otherwise will return `false`, if no `table name` previous stored.
     *
     * Does an `drop` table query if table exists.
     *
     * @return bool|int
     */
    public function dropping();

    /**
     * Modify columns in an existing database table, by either:
     *```js
     *  - array( column_name, datatype, ...value/options arguments ) // calls create_schema()
     *  - addColumn( column_name, datatype, ...value/options arguments ) // returns string
     *  - dropColumn( column_name ) // returns string
     *  - changingColumn( column_name, datatype, ...value/options arguments ) // returns string
     *```
     * @param string $table The name of the db table that you wish to alter
     * @param array ...$alteringSchema An array of:
     *
     * - @param string `$name,` - column name
     * - @param string `$type,` - data type for the column
     * - @param mixed `$size,` | `$value,`
     * - @param mixed `...$anyOtherArgs`
     *
     * @return mixed results of query() call
     */
    public function alter(string $table = null, ...$alteringSchema);

    /**
     * Preforms a `alter` method call on a already preset `table name`, and optional `prefix`
     *
     * This method **expects** either `tableSetup(name, prefix)`, `setTable(name)`, or `setPrefix(append)`
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
    public function altering(...$alteringSchema);
}
