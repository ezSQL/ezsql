<?php
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
 * and is licensed under the MIT license.
 */
namespace ezsql;

interface ezQueryInterface
{ 	
    /**
    * Clean input of XSS, html, javascript, etc...
    * @param string $string
    * @return string cleaned string
    */	        
    public static function clean($string);
      	
    /**
    * Turn on prepare function availability in ezQuery shortcut method calls 
    */    
    public function prepareActive();
    
    /**
    * Turn off prepare function availability in ezQuery shortcut method calls 
    */    
    public function prepareInActive();

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
    public function groupBy($groupBy);

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
    public function having(...$having);

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
    * @param string $condition -  
    *
    * @return bool|string JOIN sql statement, false for error
    */
    public function innerJoin(
        string $leftTable = null, 
        string $rightTable = null, 
        string $leftColumn = null, string $rightColumn = null, $condition = \EQ);

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
    * @param string $condition -  
    *
    * @return bool|string JOIN sql statement, false for error
    */
    public function leftJoin(
        string $leftTable = null, 
        string $rightTable = null, 
        string $leftColumn = null, string $rightColumn = null, $condition = \EQ);

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
    * @param string $condition -  
    *
    * @return bool|string JOIN sql statement, false for error
    */
    public function rightJoin(
        string $leftTable = null, 
        string $rightTable = null, 
        string $leftColumn = null, string $rightColumn = null, $condition = \EQ);

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
    * @param string $condition -  
    *
    * @return bool|string JOIN sql statement, false for error
    */
    public function fullJoin(
        string $leftTable = null, 
        string $rightTable = null, 
        string $leftColumn = null, string $rightColumn = null, $condition = \EQ);

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
    public function union($table = '', $columnFields = '*', ...$conditions);

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
    public function unionAll($table = '', $columnFields = '*', ...$conditions);

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
    * Helper returns an WHERE sql clause string. 
    *
    * format:
    *   `where( array(x, =, y, and, extra) )` or 
    *   `where( "x  =  y  and  extra" );` // Strings will need to be double spaced
    *
    * example: 
    *   `where( array(key, operator, value, combine, extra) );` or 
    *   `where( "key  operator  value  combine  extra" );` // Strings will need to be double spaced
    *
    * @param array $whereKeyArray
    * @param $key, - table column  
    * @param $operator, - set the operator condition, 
    *                   either '<','>', '=', '!=', '>=', '<=', '<>', 'in', 'like', 
    *                       'not like', 'between', 'not between', 'is null', 'is not null'
	* @param $value, - will be escaped
    * @param $combine, - combine additional where clauses with, 
    *               either 'AND','OR', 'NOT', 'AND NOT' 
    *                   or carry over of `value` in the case the `operator` is 'between' or 'not between'
    * @param $extra - carry over of `combine` in the case the operator is 'between' or 'not between')"
    *
	* @return mixed bool/string - WHERE SQL statement, or false on error
	*/        
    public function where( ...$whereKeyArray);
    
	/**
    * Returns an SQL string or result set, given the 
    *   - table, column fields, conditions or conditional array.
    *
    * In the following format:
    * ```
    * selecting(
    *   table,
    *   columns, 
    *   // innerJoin(), leftJoin(), rightJoin(), fullJoin() alias of
    *   joining(inner|left|right|full, leftTable, rightTable, leftColumn, rightColumn, equal condition), 
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
    * @param mixed $conditions - of the following parameters:
    *
    *   @param $joins, - join clause (type, left table, right table, left column, right column, condition = EQ)
    *   @param $whereKey, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
    *   @param $groupBy, - grouping over clause the results
    *   @param $having, - having clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
    *   @param $orderby, - ordering by clause for the query
    *   @param $limit, - limit clause the number of records
    *   @param $union/$unionAll - union clause combine the result sets and removes duplicate rows/does not remove
    *   
    * @return mixed result set - see docs for more details, or false for error
	*/
    public function selecting($table = '', $columnFields = '*', ...$conditions);

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
    public function create_select($newTable, $fromColumns, $oldTable = null, ...$fromWhere);
    
    /**
    * Does an select into statement by calling selecting method
    * @param $newTable, - new database table to be created 
    * @param $fromColumns - the columns from old database table
    * @param $oldTable - old database table 
    * @param $fromWhere, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
    *
    * @return mixed bool/result - false for error
	*/
    public function select_into($newTable, $fromColumns, $oldTable = null, ...$fromWhere);
		
	/**
	* Does an update query with an array, by conditional operator array
	* @param $table, - database table to access
	* @param $keyAndValue, - table fields, assoc array with key = value (doesn't need escaped)
	* @param $WhereKey, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
	*
	* @return mixed bool/results - false for error
	*/
    public function update($table = '', $keyAndValue, ...$whereKeys);
         
	/** 
    * Helper does the actual delete query with an array
	* @return mixed bool/results - false for error
	*/
    public function delete($table = '', ...$whereKeys);

	/**
    * Does an replace query with an array
    * @param $table, - database table to access
    * @param $keyAndValue - table fields, assoc array with key = value (doesn't need escaped)
    * @return mixed bool/id of replaced record, or false for error
	*/
    public function replace($table = '', $keyAndValue);

	/**
    * Does an insert query with an array
    * @param $table, - database table to access
    * @param $keyAndValue - table fields, assoc array with key = value (doesn't need escaped)
    * @return mixed bool/id of inserted record, or false for error
	*/
    public function insert($table = '', $keyAndValue);
    
	/**
    * Does an insert into select statement by calling insert method helper then selecting method
    * @param $toTable, - database table to insert table into 
    * @param $toColumns - the receiving columns from other table columns, leave blank for all or array of column fields
    * @param $WhereKey, - where clause ( array(x, =, y, and, extra) ) or ( "x = y and extra" )
    *
    * @return mixed bool/id of inserted record, or false for error
	*/
    public function insert_select($toTable = '', $toColumns = '*', $fromTable = null, $fromColumns = '*', ...$fromWhere);
    
   /**
    * Creates an database table and columns, by either:
    *  - array( column, datatype, ...value/options arguments ) // calls create_schema() 
    *  - column( column, datatype, ...value/options arguments ) // returns string
    *  - primary( primary_key_label, ...primaryKeys) // returns string
    *  - foreign( foreign_key_label, ...foreignKeys) // returns string
    *  - unique( unique_key_label, ...uniqueKeys) // returns string
    * 
    * @param string $table, - The name of the db table that you wish to create
    * @param mixed $schemas, - An array of:
    *
    * @param string $column|CONSTRAINT, - column name/CONSTRAINT usage for PRIMARY|FOREIGN KEY
    * @param string $type|$constraintName, - data type for column/primary|foreign constraint name
    * @param mixed $size|...$primaryForeignKeys, 
    * @param mixed $value, - column should be NULL or NOT NULL. If omitted, assumes NULL
    * @param mixed $default - Optional. It is the value to assign to the column
    * 
    * @return mixed results of query() call
    */
   public function create(string $table = null, ...$schemas);
}
