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
 
	// ezQuery prepare placeholder/positional tag
		const _TAG = '__ez__';
    // Use to set get_result output as json 
        const _JSON = 'json';
 
    /*
     * Operator boolean expressions.
     */
		const EQ  = '=';
		const NEQ = '<>';
		const NE  = '!=';
		const LT  = '<';
		const LTE = '<=';
		const GT  = '>';
		const GTE = '>=';
    
		const _IN = 'IN';
		const _notIN = 'NOT IN';
		const _LIKE = 'LIKE';
		const _notLIKE  = 'NOT LIKE';
		const _BETWEEN = 'BETWEEN';
		const _notBETWEEN = 'NOT BETWEEN';
        
		const _isNULL = 'IS NULL';
		const _notNULL  = 'IS NOT NULL';
    
    /*
     * Combine operators .
     */    
		const _AND = 'AND';
		const _OR = 'OR';
		const _NOT = 'NOT';
		const _andNOT = 'AND NOT'; 
                
        // Global class instances, will be used to create and call methods directly.
        $_ezQuery = null;
       // $_ezCubrid = null;
        $_ezMysqli = null;
       // $_ezOracle8_9 = null;
       // $_ezOracleTNS = null;
        $_ezPdo = null;
        $_ezPostgresql = null;
        $_ezRecordset = null;
        $_ezSqlite3 = null;
        $_ezSqlsrv = null;
 
	/**********************************************************************
     * Creates an array from expressions in the following formate
     * param:  strings @x,        The left expression.
     *                 @operator, One of '<', '>', '=', '!=', '>=', '<=', '<>', 'IN',, 'NOT IN', 'LIKE', 
     *                              'NOT LIKE', 'BETWEEN', 'NOT BETWEEN', 'IS', 'IS NOT', or  the constants above.
     *                 @y,        The right expression.
     *                 @and,        combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     *                 @args          for any extras
     *
     * function comparison($x, $operator, $y, $and=null, ...$args)
     *  {
     *          return array($x, $operator, $y, $and, ...$args);
     * }    
     * @returns: array
     ***********************************************************************/
    
    /**
     * Creates an equality comparison expression with the given arguments.
     */
    function eq($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, EQ, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a non equality comparison expression with the given arguments.
     */
    function neq($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, NEQ, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates the other non equality comparison expression with the given arguments.
     */
    function ne($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, NE, $y, $and, ...$args);
        return $expression;
    }
    
    /**
     * Creates a lower-than comparison expression with the given arguments.
     */
    function lt($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, LT, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a lower-than-equal comparison expression with the given arguments.
     */
    function lte($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, LTE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a greater-than comparison expression with the given arguments.
     */
    function gt($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, GT, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a greater-than-equal comparison expression with the given arguments.
     */
    function gte($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, GTE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates an IS NULL expression with the given arguments.
     */
    function isNull($x, $y='null', $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _isNULL, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates an IS NOT NULL expression with the given arguments.
     */
    function isNotNull($x, $y='null', $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _notNULL, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a LIKE() comparison expression with the given arguments.
     */
    function like($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _LIKE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT LIKE() comparison expression with the given arguments.
     */
    function notLike($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _notLIKE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a IN () comparison expression with the given arguments.
     */
    function in($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _IN, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT IN () comparison expression with the given arguments.
     */
    function notIn($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _notIN, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a BETWEEN () comparison expression with the given arguments.
     */
    function between($x, $y, $y2, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _BETWEEN,$y, $y2, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT BETWEEN () comparison expression with the given arguments.
     */
    function notBetween($x, $y, $y2, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _notBETWEEN, $y, $y2, ...$args);
        return $expression;
    }
    
    /**
       * desc: Using global class instances, setup functions to call class methods directly.
       * param: @ezSQL - string, representing class  'cubrid', 'mysqli', 'oracle8_9', 'oracletns', 'pdo', 'postgresql', 'recordset', 'sqlite3', 'sqlsrv'
       * returns: boolean - true, or false for error
       */
    function setQuery($ezSQL='') {
        global $_ezQuery, $_ezMysqli;// $_ezCubrid, $_ezOracle8_9, $_ezOracleTNS; 'recordset' ,'oracle8_9', 'oracletns',
        global $_ezPdo, $_ezPostgresql, $_ezRecordset, $_ezSqlite3, $_ezSqlsrv;
        if (in_array(strtolower($ezSQL), array( 'cubrid', 'mysqli', 'pdo', 'postgresql', 'sqlite3', 'sqlsrv' ))) {
            switch(strtolower($ezSQL)) {
            //    case 'cubrid':
            //        $_ezQuery = $_ezCubrid;
            //        break;
                case 'mysqli':
                    $_ezQuery = $_ezMysqli;
                    break;
            //    case 'oracle8_9':
            //        $_ezQuery = $_ezOracle8_9;
            //        break;
            //    case 'oracletns':
            //        $_ezQuery = $_ezOracleTNS;
            //        break;
                case 'pdo':
                    $_ezQuery = $_ezPdo;
                    break;
                case 'postgresql':
                    $_ezQuery = $_ezPostgresql;
                    break;
                case 'recordset':
                    $_ezQuery = $_ezRecordset;
                    break;
                case 'sqlite3':
                    $_ezQuery = $_ezSqlite3;
                    break;
                case 'sqlsrv':
                    $_ezQuery = $_ezSqlsrv;
                    break;                    
            }
            return (!empty($_ezQuery)) ? true: false;            
        } else {
			$_ezQuery = null;
            unset($_ezQuery);
            return false;            
        }
    }     
    
    function select($table='', $columns='*', ...$args) {
        global $_ezQuery;
        return ($_ezQuery) ? $_ezQuery->selecting($table, $columns, ...$args) : false;
    } 
    
    function select_into($newtable, $fromcolumns='*', $oldtable=null, ...$args) {
        global $_ezQuery;
        return ($_ezQuery) ? $_ezQuery->select_into($newtable, $fromcolumns, $oldtable, ...$args) : false;
    } 
    
    function insert_select($totable='', $tocolumns='*', $fromtable, $fromcolumns='*', ...$args) {
        global $_ezQuery;
        return ($_ezQuery) ? $_ezQuery->insert_select($totable, $tocolumns, $fromtable, $fromcolumns, ...$args) : false;
    }     
    
    function create_select($newtable, $fromcolumns, $oldtable=null, ...$args) {
        global $_ezQuery;
        return ($_ezQuery) ? $_ezQuery->create_select($newtable, $fromcolumns, $oldtable, ...$args) : false;
    }  
    
    function where( ...$args) {
        global $_ezQuery;
        return ($_ezQuery) ? $_ezQuery->where( ...$args) : false;
    } 
    
    function groupBy($groupBy) {
        global $_ezQuery;
        return ($_ezQuery) ? $_ezQuery->groupBy($groupBy) : false;
    } 
    
    function having( ...$args) {
        global $_ezQuery;
        return ($_ezQuery) ? $_ezQuery->having( ...$args) : false;
    }
    
    function orderBy($orderBy, $order) {
        global $_ezQuery;
        return ($_ezQuery) ? $_ezQuery->orderBy($orderBy, $order) : false;
    } 
    
    function insert($table='', $keyvalue) {
        global $_ezQuery;
        return ($_ezQuery) ? $_ezQuery->insert($table, $keyvalue) : false;
    } 
    
    function update($table='', $keyvalue, ...$args) {
        global $_ezQuery;
        return ($_ezQuery) ? $_ezQuery->update($table, $keyvalue, ...$args) : false;
    } 
    
    function delete($table='', ...$args) {
        global $_ezQuery;
        return ($_ezQuery) ? $_ezQuery->delete($table, ...$args) : false;
    } 
        
    function replace($table='', $keyvalue) {
        global $_ezQuery;
        return ($_ezQuery) ? $_ezQuery->replace($table, $keyvalue) : false;
    }  

