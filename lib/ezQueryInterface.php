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
    public function clean($string);
    
    /*
    * Return status of prepare function availability in method calls
    */
    public function getPrepare();
  	
    /*
    * Turn off/on prepare function availability in ezQuery method calls 
    */
    public function setPrepare($on = true);
    
    /**
     * Returns array of parameter values for prepare function 
     * @return array
     */
    public function getParameters();
    
    /**
    * Add parameter values to class array variable for prepare function.
    * @param mixed $valueToAdd
    *
    * @return int array count
    */
    public function setParameters($valueToAdd = null);
    
    /**
    * Clear parameter values
    *
    * @return bool false
    */
    public function clearParameters();

    /**
    * Convert array to string, and attach '`, `' for separation.
    *
    * @return string
    */  
    public function to_string($arrays);
            
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
    * format having( array(x, =, y, and, extra) ) or having( "x  =  y  and  extra" );    
	* example: having( array(key, operator, value, combine, extra) ); or having( "key operator value combine extra" );
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
    * Specifies an ordering for the query results.  
    * @param string $orderBy - The column. 
    * @param string $order - The ordering direction. 
    *
    * @return string - ORDER BY SQL statement, or false on error
    */
    public function orderBy($orderBy, $order);

    /**
    * Specifies records from one or more tables in a database and limit the number of records returned.  
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
    *   `where( array(x, =, y, and, extra) ) or where( "x  =  y  and  extra" );`
    *
    * example: 
    *   `where( array(key, operator, value, combine, extra) );` or `where( "key operator value combine extra" );`
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
    * Returns an sql string or result set given the table, fields, by operator condition or conditional array.
    *
    * ```
    * selecting(
    *   table,
    *   columns, 
    *   where( eq( columns, values, _AND ), like( columns, _d ) ), 
    *   groupBy( columns ), 
    *   having( between( columns, values1, values2 ) ), 
    *   orderBy( columns, desc ),
    *   limit( numberOfRecords, offset )
    *);
    * ``` 
    *
    * @param $table, - database table to access
    * @param $fields, - table columns, string or array
    * @param $WhereKey, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
    * @param $groupBy, - grouping over the results
    * @param $having, - having clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
    * @param $orderby - ordering for the query
    * @param $limit - limit the number of records
    *   
    * @return result set - see docs for more details, or false for error
	*/
    public function selecting($table = '', $fields = '*', ...$get_args);
	
    /**
     * Get sql statement from selecting method instead of executing get_result
     * @return string
     */
    public function select_sql($table = '', $fields = '*', ...$get_args);
    
	/** 
    * Does an create select statement by calling selecting method
    *
    * @param $newTable, - new database table to be created 
    * @param $fromColumns - the columns from old database table
    * @param $oldTable - old database table 
    * @param $WhereKey, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
    *   example: where( array(key, operator, value, combine, extra) ); or where( "key operator value combine extra" );
    *
    * @return mixed bool/result
	*/
    public function create_select($newTable, $fromColumns, $oldTable = null, ...$fromWhere);
    
    /**
    * Does an select into statement by calling selecting method
    * @param $newTable, - new database table to be created 
    * @param $fromColumns - the columns from old database table
    * @param $oldTable - old database table 
    * @param $WhereKey, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
	*   example: where( array(key, operator, value, combine, extra) ); or where( "key operator value combine extra" );
    * @return mixed bool/result
	*/
    public function select_into($newTable, $fromColumns, $oldTable = null, ...$fromWhere);
		
	/**
	* Does an update query with an array, by conditional operator array
	* @param $table, - database table to access
	* @param $keyAndValue, - table fields, assoc array with key = value (doesn't need escaped)
	* @param $WhereKey, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
	*   example: where( array(key, operator, value, combine, extra) ); or where( "key operator value combine extra" );
	* @return mixed bool/results - false for error
	*/
    public function update($table = '', $keyAndValue, ...$WhereKeys);
         
	/** 
    * Helper does the actual delete query with an array
	* @return mixed bool/results - false for error
	*/
    public function delete($table = '', ...$WhereKeys);
    
	/**
    * Helper does the actual insert or replace query with an array
	* @return mixed bool/results - false for error
	*/
    //public function _query_insert_replace($table = '', $keyAndValue, $type = '', $execute = true);
        
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
    *   example: where( array(key, operator, value, combine, extra) ); or where( "key operator value combine extra" );
    * @return mixed bool/id of inserted record, or false for error
	*/
    public function insert_select($toTable = '', $toColumns = '*', $fromTable = null, $fromColumns = '*', ...$fromWhere);
}
