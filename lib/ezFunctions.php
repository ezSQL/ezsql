<?php

use ezsql\ezQueryInterface;

global $ezInstance;

if (!function_exists('ezFunctions')) {
    // Global class instances, will be used to create and call methods directly.

	/**
     * Creates an array from expressions in the following format
     * @param  strings @x,  The left expression.
     *                 @operator, One of '<', '>', '=', '!=', '>=', '<=', '<>', 'IN',, 'NOT IN', 'LIKE', 
     *                              'NOT LIKE', 'BETWEEN', 'NOT BETWEEN', 'IS', 'IS NOT', or from constants.php.
     *                 @y,  The right expression.
     *                 @and,    combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     *                 @args    for any extras
     *
     * function comparison($x, $operator, $y, $and = null, ...$args)
     *  {
     *      return array($x, $operator, $y, $and, ...$args);
     *  }
     * 
     * @return array
     */
    
    /**
     * Creates an equality comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function eq($x, $y, $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \EQ, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a non equality comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function neq($x, $y, $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \NEQ, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates the other non equality comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function ne($x, $y, $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \NE, $y, $and, ...$args);
        return $expression;
    }
    
    /**
     * Creates a lower-than comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function lt($x, $y, $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \LT, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a lower-than-equal comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function lte($x, $y, $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \LTE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a greater-than comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function gt($x, $y, $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \GT, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a greater-than-equal comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function gte($x, $y, $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \GTE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates an IS NULL expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function isNull($x, $y ='null', $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \_isNULL, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates an IS NOT NULL expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function isNotNull($x, $y = 'null', $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \_notNULL, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a LIKE() comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function like($x, $y, $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \_LIKE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT LIKE() comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function notLike($x, $y, $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \_notLIKE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a IN () comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function in($x, $y, $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \_IN, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT IN () comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function notIn($x, $y, $and = null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \_notIN, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a BETWEEN () comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function between($x, $y, $y2, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \_BETWEEN, $y, $y2, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT BETWEEN () comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function notBetween($x, $y, $y2, ...$args)
    {
        $expression = array();
        array_push($expression, $x, \_notBETWEEN, $y, $y2, ...$args);
        return $expression;
    }
    
    /**
    * Using global class instances, setup functions to call class methods directly.
    * @param string|object $ezSQL - Representing a SQL database or class instance 
    * @return boolean
    */
    function setInstance($ezSQL = ''): bool {
        global $ezInstance;
        $status = false;

        if ($ezSQL instanceOf ezQueryInterface) {
			$ezInstance = $ezSQL;
			$status = true;
		} elseif (array_key_exists(strtolower($ezSQL), \VENDOR)) {
            if (!empty($GLOBALS['db_'.strtolower($ezSQL)]))
                $ezInstance = $GLOBALS['db_'.strtolower($ezSQL)];
            $status = !empty($ezInstance);            
        } elseif (!empty($GLOBALS['ezInstance'])) {
            unset($GLOBALS['ezInstance']);
        }

        return $status;
    }
    
    function getInstance() {
        global $ezInstance;

        return $ezInstance;
    }     
    
    function cleanInput($string) {
        return ezQuery::clean($string);
    } 

    function select($table = '', $columns = '*', ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->selecting($table, $columns, ...$args) 
            : false;
    } 
    
    function select_into($table, $columns = '*', $old = null, ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->select_into($table, $columns, $old, ...$args) 
            : false;
    } 
    
    function insert_select($totable = '', $columns = '*', $fromTable, $from = '*', ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->insert_select($totable, $columns, $fromTable, $from, ...$args) 
            : false;
    }     
    
    function create_select($table, $from, $old = null, ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->create_select($table, $from, $old, ...$args) 
            : false;
    }  
    
    function where( ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->where( ...$args) 
            : false;
    } 
    
    function groupBy($groupBy) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->groupBy($groupBy) 
            : false;
    } 
    
    function having( ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
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
        return ($ezQuery instanceOf ezQueryInterface) 
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
        return ($ezQuery instanceOf ezQueryInterface) 
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
        return ($ezQuery instanceOf ezQueryInterface) 
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
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->fullJoin($leftTable, $rightTable, $leftColumn, $rightColumn, $condition) 
            : false;
    }

    function union($table = '', $columnFields = '*', ...$conditions) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->union($table, $columnFields, ...$conditions) 
            : false;
    } 

    function unionAll($table = '', $columnFields = '*', ...$conditions) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->unionAll($table, $columnFields, ...$conditions) 
            : false;
    } 

    function orderBy($orderBy, $order) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->orderBy($orderBy, $order) 
            : false;
    } 

    function limit($numberOf, $offset = null) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->limit($numberOf, $offset) 
            : false;
    } 
    
    function insert($table = '', $keyValue) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->insert($table, $keyValue) 
            : false;
    } 
    
    function update($table = '', $keyValue, ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->update($table, $keyValue, ...$args) 
            : false;
    } 
    
    function delete($table = '', ...$args) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->delete($table, ...$args) 
            : false;
    } 
        
    function replace($table = '', $keyValue) {
        $ezQuery = \getInstance();
        return ($ezQuery instanceOf ezQueryInterface) 
            ? $ezQuery->replace($table, $keyValue) 
            : false;
    }  

    function ezFunctions() {
        return true;
    }
}
