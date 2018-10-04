<?php
/**
 * @author  Lawrence Stubbs <technoexpressnet@gmail.com>
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
use ezsql\Constants;
                
    // Global class instances, will be used to create and call methods directly.
    global $_ezQuery;
    $_ezQuery = null;

	/**
     * Creates an array from expressions in the following format
     * @param  strings @x,        The left expression.
     *                 @operator, One of '<', '>', '=', '!=', '>=', '<=', '<>', 'IN',, 'NOT IN', 'LIKE', 
     *                              'NOT LIKE', 'BETWEEN', 'NOT BETWEEN', 'IS', 'IS NOT', or  the constants above.
     *                 @y,        The right expression.
     *                 @and,        combine additional expressions with,  'AND','OR', 'NOT', 'AND NOT'.
     *                 @args          for any extras
     *
     * function comparison($x, $operator, $y, $and=null, ...$args)
     *  {
     *          return array($x, $operator, $y, $and, ...$args);
     *  }    
     * @return array
     */
    
    /**
     * Creates an equality comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function eq($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, EQ, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a non equality comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function neq($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, NEQ, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates the other non equality comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function ne($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, NE, $y, $and, ...$args);
        return $expression;
    }
    
    /**
     * Creates a lower-than comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function lt($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, LT, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a lower-than-equal comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function lte($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, LTE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a greater-than comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function gt($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, GT, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a greater-than-equal comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function gte($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, GTE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates an IS NULL expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function isNull($x, $y='null', $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _isNULL, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates an IS NOT NULL expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function isNotNull($x, $y='null', $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _notNULL, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a LIKE() comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function like($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _LIKE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT LIKE() comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function notLike($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _notLIKE, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a IN () comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function in($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _IN, $y, $and, ...$args);
        return $expression;
    }

    /**
     * Creates a NOT IN () comparison expression with the given arguments.
     * 
     * @param  strings 
     * @return array
     */
    function notIn($x, $y, $and=null, ...$args)
    {
        $expression = array();
        array_push($expression, $x, _notIN, $y, $and, ...$args);
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
        array_push($expression, $x, _BETWEEN,$y, $y2, ...$args);
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
        array_push($expression, $x, _notBETWEEN, $y, $y2, ...$args);
        return $expression;
    }
    
    /**
    * Using global class instances, setup functions to call class methods directly.
    * @param @ezSQL - string, representing sql database class
    * @return boolean
    */
    function setQuery($ezSQL='') {
        global $_ezQuery;
        $status = false;
        if (array_key_exists(strtolower($ezSQL), _DATABASES)) {
            if (!empty($GLOBALS['db_'.strtolower($ezSQL)]))
                $_ezQuery = $GLOBALS['db_'.strtolower($ezSQL)];
            $status = !empty($_ezQuery) ? true: false;            
        } elseif (!empty($GLOBALS['_ezQuery'])) {
            unset($GLOBALS['_ezQuery']);
        }
        return $status;
    }     
    
    function select($table='', $columns='*', ...$args) {
        global $_ezQuery;
        return !empty($_ezQuery) ? $_ezQuery->selecting($table, $columns, ...$args) : false;
    } 
    
    function select_into($newtable, $fromcolumns='*', $oldtable=null, ...$args) {
        global $_ezQuery;
        return !empty($_ezQuery) ? $_ezQuery->select_into($newtable, $fromcolumns, $oldtable, ...$args) : false;
    } 
    
    function insert_select($totable='', $tocolumns='*', $fromtable, $fromcolumns='*', ...$args) {
        global $_ezQuery;
        return !empty($_ezQuery) ? $_ezQuery->insert_select($totable, $tocolumns, $fromtable, $fromcolumns, ...$args) : false;
    }     
    
    function create_select($newtable, $fromcolumns, $oldtable=null, ...$args) {
        global $_ezQuery;
        return !empty($_ezQuery) ? $_ezQuery->create_select($newtable, $fromcolumns, $oldtable, ...$args) : false;
    }  
    
    function where( ...$args) {
        global $_ezQuery;
        return !empty($_ezQuery) ? $_ezQuery->where( ...$args) : false;
    } 
    
    function groupBy($groupBy) {
        global $_ezQuery;
        return !empty($_ezQuery) ? $_ezQuery->groupBy($groupBy) : false;
    } 
    
    function having( ...$args) {
        global $_ezQuery;
        return !empty($_ezQuery) ? $_ezQuery->having( ...$args) : false;
    }
    
    function orderBy($orderBy, $order) {
        global $_ezQuery;
        return !empty($_ezQuery) ? $_ezQuery->orderBy($orderBy, $order) : false;
    } 
    
    function insert($table='', $keyvalue) {
        global $_ezQuery;
        return !empty($_ezQuery) ? $_ezQuery->insert($table, $keyvalue) : false;
    } 
    
    function update($table='', $keyvalue, ...$args) {
        global $_ezQuery;
        return !empty($_ezQuery) ? $_ezQuery->update($table, $keyvalue, ...$args) : false;
    } 
    
    function delete($table='', ...$args) {
        global $_ezQuery;
        return !empty($_ezQuery) ? $_ezQuery->delete($table, ...$args) : false;
    } 
        
    function replace($table='', $keyvalue) {
        global $_ezQuery;
        return !empty($_ezQuery) ? $_ezQuery->replace($table, $keyvalue) : false;
    }  

