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

class ezQuery
{ 		
	protected $select_result = true;
	protected $prepareActive = false;
    
	private $fromtable = null;
    private $iswhere = true;    
    private $isinto = false;
    
    function __construct()
		{
		}
        
    function clean($string) 
    {
        $patterns = array( // strip out:
                '@<script[^>]*?>.*?</script>@si', // Strip out javascript
                '@<[\/\!]*?[^<>]*?>@si',          // HTML tags
                '@<style[^>]*?>.*?</style>@siU',  // Strip style tags properly
                '@<![\s\S]*?--[ \t\n\r]*>@'       // Strip multi-line comments
                );
                
        $string = preg_replace($patterns,'',$string);
        $string = trim($string);
        $string = stripslashes($string);
        
        return htmlentities($string);
    }
    
    // return status of prepare function availability in method calls
    function getPrepare($on=true) {
        return $this->prepareActive;
	}
  	
    // turn off/on prepare function availability in ezQuery method calls 
    function setPrepare($on=true) {
        $this->prepareActive = ($on) ? true : false;
		return null;
	}  	
    
    // returns array of parameter values for prepare function 
    function getParamaters() {
		return $this->preparedvalues;
	}
    
    /**
        * desc: add parameter values to class array variable for prepare function or clear if no value supplied
        * param: @valuetoadd mixed
        *
        * returns int - array count
        */
    function setParamaters($valuetoadd=null) {
        if (empty($valuetoadd)) {
            $this->preparedvalues = array();
            return null;
        } else 
            return array_push($this->preparedvalues, $valuetoadd); 
	}
    
    function to_string($arrays) {        
        if (is_array( $arrays )) {
            $columns = '';
            foreach($arrays as $val) {
                $columns .= $val.', ';
            }
            $columns = rtrim($columns, ', ');            
        } else
            $columns = $arrays;
        return $columns;
    }
            
    /**
    * desc: specifies a grouping over the results of the query.
    * <code>
    *     $this->selecting('table', 
    *                   columns,
    *                   where(columns  =  values),
    *                   groupBy(columns),
    *                   having(columns  =  values),
    *                   orderBy(order);
    * </code>
    * param: mixed @groupBy The grouping expression.  
	*
    * returns: string - GROUP BY SQL statement, or false on error
    */
    function groupBy($groupBy)
    {
        if (empty($groupBy)) {
            return false;
        }
        
        $columns = $this->to_string($groupBy);
        
        return 'GROUP BY ' .$columns;
    }

    /**
    * desc: specifies a restriction over the groups of the query. 
	* formate: having( array(x, =, y, and, extra) ) or having( "x  =  y  and  extra" );
	* example: having( array(key, operator, value, combine, extra) ); or having( "key operator value combine extra" );
    * param: mixed @array or @string double spaced "(key, - table column  
    *        	operator, - set the operator condition, either '<','>', '=', '!=', '>=', '<=', '<>', 'in', 'like', 'between', 'not between', 'is null', 'is not null'
	*		value, - will be escaped
    *        	combine, - combine additional where clauses with, either 'AND','OR', 'NOT', 'AND NOT' or  carry over of @value in the case the @operator is 'between' or 'not between'
	*		extra - carry over of @combine in the case the operator is 'between' or 'not between')"
    * @returns: string - HAVING SQL statement, or false on error
    */
    function having(...$having)
    {
        $this->iswhere = false;
        return $this->where( ...$having);
    }
 
    /**
    * desc: specifies an ordering for the query results.  
    * param:  @order The ordering direction. 
    * returns: string - ORDER BY SQL statement, or false on error
    */
    function orderBy($orderBy, $order)
    {
        if (empty($orderBy)) {
            return false;
        }
        
        $columns = $this->to_string($orderBy);
        
        $order = (in_array(strtoupper($order), array( 'ASC', 'DESC'))) ? strtoupper($order) : 'ASC';
        
        return 'ORDER BY '.$columns.' '. $order;
    }
   
 	/**********************************************************************
         * desc: helper returns an WHERE sql clause string 
	* formate: where( array(x, =, y, and, extra) ) or where( "x  =  y  and  extra" );
	* example: where( array(key, operator, value, combine, extra) ); or where( "key operator value combine extra" );
	* param: mixed @array or @string double spaced "(key, - table column  
         *        	operator, - set the operator condition, either '<','>', '=', '!=', '>=', '<=', '<>', 'in', 'like', 'not like', 'between', 'not between', 'is null', 'is not null'
	*		value, - will be escaped
         *        	combine, - combine additional where clauses with, either 'AND','OR', 'NOT', 'AND NOT' or  carry over of @value in the case the @operator is 'between' or 'not between'
	*		extra - carry over of @combine in the case the operator is 'between' or 'not between')"
         * returns: string - WHERE SQL statement, or false on error
	*/        
    function where( ...$getwherekeys) {      
        $whereorhaving = ($this->iswhere) ? 'WHERE' : 'HAVING';
        $this->iswhere = true;
        
		if (!empty($getwherekeys)){
			if (is_string($getwherekeys[0])) {
				foreach ($getwherekeys as $makearray) 
					$wherekeys[] = explode('  ',$makearray);	
			} else 
				$wherekeys = $getwherekeys;			
		} else 
			return '';
		
		foreach ($wherekeys as $values) {
			$operator[] = (isset($values[1])) ? $values[1]: '';
			if (!empty($values[1])){
				if (strtoupper($values[1]) == 'IN') {
					$wherekey[ $values[0] ] = array_slice($values,2);
					$combiner[] = (isset($values[3])) ? $values[3]: _AND;
					$extra[] = (isset($values[4])) ? $values[4]: null;				
				} else {
					$wherekey[ (isset($values[0])) ? $values[0] : '1' ] = (isset($values[2])) ? $values[2] : '' ;
					$combiner[] = (isset($values[3])) ? $values[3]: _AND;
					$extra[] = (isset($values[4])) ? $values[4]: null;
				}				
			} else {
                $this->setParamaters();
				return false;
            }                
		}
        
        $where='1';    
        if (! isset($wherekey['1'])) {
            $where='';
            $i=0;
            $needtoskip=false;
            foreach($wherekey as $key=>$val) {
                $iscondition = strtoupper($operator[$i]);
				$combine = $combiner[$i];
				if ( in_array(strtoupper($combine), array( 'AND', 'OR', 'NOT', 'AND NOT' )) || isset($extra[$i])) 
					$combinewith = (isset($extra[$i])) ? $combine : strtoupper($combine);
				else 
					$combinewith = _AND;
                if (! in_array( $iscondition, array( '<', '>', '=', '!=', '>=', '<=', '<>', 'IN', 'LIKE', 'NOT LIKE', 'BETWEEN', 'NOT BETWEEN', 'IS', 'IS NOT' ) )) {
                    $this->setParamaters();
                    return false;
                } else {
                    if (($iscondition=='BETWEEN') || ($iscondition=='NOT BETWEEN')) {
						$value = $this->escape($combinewith);
						if (in_array(strtoupper($extra[$i]), array( 'AND', 'OR', 'NOT', 'AND NOT' ))) 
							$mycombinewith = strtoupper($extra[$i]);
						else 
                            $mycombinewith = _AND;
						if ($this->getPrepare()) {
							$where.= "$key ".$iscondition.' '._TAG." AND "._TAG." $mycombinewith ";
							$this->setParamaters($val);
							$this->setParamaters($combinewith);
						} else 
							$where.= "$key ".$iscondition." '".$this->escape($val)."' AND '".$value."' $mycombinewith ";
						$combinewith = $mycombinewith;
					} elseif ($iscondition=='IN') {
						$value = '';
						foreach ($val as $invalues) {
							if ($this->getPrepare()) {
								$value .= _TAG.', ';
								$this->setParamaters($invalues);
							} else 
								$value .= "'".$this->escape($invalues)."', ";
						}													
						$where.= "$key ".$iscondition." ( ".rtrim($value, ', ')." ) $combinewith ";
					} elseif(((strtolower($val)=='null') || ($iscondition=='IS') || ($iscondition=='IS NOT'))) {
                        $iscondition = (($iscondition=='IS') || ($iscondition=='IS NOT')) ? $iscondition : 'IS';
                        $where.= "$key ".$iscondition." NULL $combinewith ";
                    } elseif((($iscondition=='LIKE') || ($iscondition=='NOT LIKE')) && ! preg_match('/[_%?]/',$val)) return false;
                    else {
						if ($this->getPrepare()) {
							$where.= "$key ".$iscondition.' '._TAG." $combinewith ";
							$this->setParamaters($val);
						} else 
							$where.= "$key ".$iscondition." '".$this->escape($val)."' $combinewith ";
					}
                    $i++;
                }
            }
            $where = rtrim($where, " $combinewith ");
        }
		
        if (($this->getPrepare()) && !empty($this->getParamaters()) && ($where!='1'))
			return " $whereorhaving ".$where.' ';
		else
			return ($where!='1') ? " $whereorhaving ".$where.' ' : ' ' ;
    }        
    
	/**********************************************************************
    * desc: returns an sql string or result set given the table, fields, by operator condition or conditional array
    *<code>
    *selecting('table', 
    *        'columns',
    *        where( eq( 'columns', values, _AND ), like( 'columns', _d ) ),
    *        groupBy( 'columns' ),
    *        having( between( 'columns', values1, values2 ) ),
    *        orderBy( 'columns', 'desc' );
    *</code>    
    *
    * param: @table, - database table to access
    *        @fields, - table columns, string or array
    *        @wherekey, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
    *        @groupby, - 
    *        @having, - having clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
    *        @orderby - 	*   
    * returns: a result set - see docs for more details, or false for error
	*/
    function selecting($table='', $fields='*', ...$get_args) {    
		$getfromtable = $this->fromtable;
		$getselect_result = $this->select_result;       
		$getisinto = $this->isinto;
        
		$this->fromtable = null;
		$this->select_result = true;	
		$this->isinto = false;	
        
        $skipwhere = false;
        $wherekeys = $get_args;
        $where = '';
		
        if ( ! isset($table) || $table=='' ) {
            $this->setParamaters();
            return false;
        }
        
        $columns = $this->to_string($fields);
        
		if (isset($getfromtable) && ! $getisinto) 
			$sql="CREATE TABLE $table AS SELECT $columns FROM ".$getfromtable;
        elseif (isset($getfromtable) && $getisinto) 
			$sql="SELECT $columns INTO $table FROM ".$getfromtable;
        else 
			$sql="SELECT $columns FROM ".$table;

        if (!empty($get_args)) {
			if (is_string($get_args[0])) {
                $args_by = '';
                $groupbyset = false;      
                $havingset = false;             
                $orderbyset = false;   
				foreach ($get_args as $where_groupby_having_orderby) {
                    if (strpos($where_groupby_having_orderby,'WHERE')!==false ) {
                        $args_by .= $where_groupby_having_orderby;
                        $skipwhere = true;
                    } elseif (strpos($where_groupby_having_orderby,'GROUP BY')!==false ) {
                        $args_by .= ' '.$where_groupby_having_orderby;
                        $groupbyset = true;
                    } elseif (strpos($where_groupby_having_orderby,'HAVING')!==false ) {
                        if ($groupbyset) {
                            $args_by .= ' '.$where_groupby_having_orderby;
                            $havingset = true;
                        } else {
                            $this->setParamaters();
                            return false;
                        }
                    } elseif (strpos($where_groupby_having_orderby,'ORDER BY')!==false ) {
                        $args_by .= ' '.$where_groupby_having_orderby;    
                        $orderbyset = true;
                    }
                }
                if ($skipwhere || $groupbyset || $havingset || $orderbyset) {
                    $where = $args_by;
                    $skipwhere = true;
                }
			}		
		} else {
            $skipwhere = true;
        }        
        
        if (! $skipwhere)
            $where = $this->where( ...$wherekeys);
        
        if (is_string($where)) {
            $sql .= $where;
            if ($getselect_result) 
                return (($this->getPrepare()) && !empty($this->getParamaters())) ? $this->get_results($sql, OBJECT, true) : $this->get_results($sql);     
            else 
                return $sql;
        } else {
            $this->setParamaters();
            return false;
        }             
    }
	
    // Returns: string - sql statement from selecting method instead of executing get_result
    function select_sql($table='', $fields='*', ...$get_args) {
		$this->select_result = false;
        return $this->selecting($table, $fields, ...$get_args);	            
    }
    
	/**********************************************************************
    * desc: does an create select statement by calling selecting method
    * param: @newtable, - new database table to be created 
    *	@fromcolumns - the columns from old database table
    *	@oldtable - old database table 
    *        @wherekey, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
    *   example: where( array(key, operator, value, combine, extra) ); or where( "key operator value combine extra" );
    * returns: 
	*/
    function create_select($newtable, $fromcolumns, $oldtable=null, ...$fromwhere) {
		if (isset($oldtable))
			$this->fromtable = $oldtable;
		else {
            $this->setParamaters();
			return false;            
        }
			
        $newtablefromtable = $this->select_sql($newtable, $fromcolumns, ...$fromwhere);			
        if (is_string($newtablefromtable))
            return (($this->getPrepare()) && !empty($this->getParamaters())) ? $this->query($newtablefromtable, true) : $this->query($newtablefromtable); 
        else {
            $this->setParamaters();
            return false;    		
        }
    }
    
    /**********************************************************************
    * desc: does an select into statement by calling selecting method
    * param: @newtable, - new database table to be created 
    *	@fromcolumns - the columns from old database table
    *	@oldtable - old database table 
    *        @wherekey, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
	*   example: where( array(key, operator, value, combine, extra) ); or where( "key operator value combine extra" );
    * returns: 
	*/
    function select_into($newtable, $fromcolumns, $oldtable=null, ...$fromwhere) {
		$this->isinto = true;        
		if (isset($oldtable))
			$this->fromtable = $oldtable;
		else {
			$this->setParamaters();
            return false;          			
		}  
			
        $newtablefromtable = $this->select_sql($newtable, $fromcolumns, ...$fromwhere);
        if (is_string($newtablefromtable))
            return (($this->getPrepare()) && !empty($this->getprepared())) ? $this->query($newtablefromtable, true) : $this->query($newtablefromtable); 
        else {
			$this->setParamaters();
            return false;          			
		}  
    }
		
	/**********************************************************************
	* desc: does an update query with an array, by conditional operator array
	* param: @table, - database table to access
	*	@keyandvalue, - table fields, assoc array with key = value (doesn't need escaped)
	*   @wherekey, - where clause ( array(x, =, y, and, extra) ) or ( "x  =  y  and  extra" )
	*		example: where( array(key, operator, value, combine, extra) ); or where( "key operator value combine extra" );
	* returns: (query_id) for fetching results etc, or false for error
	*/
    function update($table='', $keyandvalue, ...$wherekeys) {        
        if ( ! is_array( $keyandvalue ) || ! isset($table) || $table=='' ) {
			$this->setParamaters();
            return false;
        }
        
        $sql="UPDATE $table SET ";
        
        foreach($keyandvalue as $key=>$val) {
            if(strtolower($val)=='null') {
				$sql.= "$key = NULL, ";
            } elseif(in_array(strtolower($val), array( 'current_timestamp()', 'date()', 'now()' ))) {
				$sql.= "$key = CURRENT_TIMESTAMP(), ";
			} else {
				if ($this->getPrepare()) {
					$sql.= "$key = "._TAG.", ";
					$this->setParamaters($val);
				} else 
					$sql.= "$key = '".$this->escape($val)."', ";
			}
        }
        
        $where = $this->where(...$wherekeys);
        if (is_string($where)) {   
            $sql = rtrim($sql, ', ') . $where;
            return (($this->getPrepare()) && !empty($this->getParamaters())) ? $this->query($sql, true) : $this->query($sql) ;       
        } else {
			$this->setParamaters();
            return false;
		}
    }   
         
	/**********************************************************************
         * desc: helper does the actual insert or replace query with an array
	*/
    function delete($table='', ...$wherekeys) {   
        if ( empty($table) ) {
			$this->setParamaters();
            return false;          			
		}  
		
        $sql="DELETE FROM $table";
        
        $where = $this->where(...$wherekeys);
        if (is_string($where)) {   
            $sql .= $where;						
            return (($this->getPrepare()) && !empty($this->getParamaters())) ? $this->query($sql, true) : $this->query($sql) ;  
        } else {
			$this->setParamaters();
            return false;          			
		}  
    }
    
	/**********************************************************************
         * desc: helper does the actual insert or replace query with an array
	*/
    function _query_insert_replace($table='', $keyandvalue, $type='', $execute=true) {  
        if ((! is_array($keyandvalue) && ($execute)) || $table=='' ) {
			$this->setParamaters();
            return false;          			
		}  
        
        if ( ! in_array( strtoupper( $type ), array( 'REPLACE', 'INSERT' ))) {
			$this->setParamaters();
            return false;          			
		}  
            
        $sql="$type INTO $table";
        $v=''; $n='';

        if ($execute) {
            foreach($keyandvalue as $key=>$val) {
                $n.="$key, ";
                if(strtolower($val)=='null') $v.="NULL, ";
                elseif(in_array(strtolower($val), array( 'current_timestamp()', 'date()', 'now()' ))) $v.="CURRENT_TIMESTAMP(), ";
                else  {
					if ($this->getPrepare()) {
						$v.= _TAG.", ";
						$this->setParamaters($val);
					} else 
						$v.= "'".$this->escape($val)."', ";
				}               
            }
            
            $sql .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";

			if (($this->getPrepare()) && !empty($this->getParamaters())) 
				$ok = $this->query($sql, true);
			else 
				$ok = $this->query($sql);
				
            if ($ok)
                return $this->insert_id;
            else {
				$this->setParamaters();
				return false;          			
			}  
        } else {
            if (is_array($keyandvalue)) {
                if (array_keys($keyandvalue) === range(0, count($keyandvalue) - 1)) {
                    foreach($keyandvalue as $key) {
                        $n.="$key, ";                
                    }
                    $sql .= " (". rtrim($n, ', ') .") ";                         
                } else {
					return false;          			
				}          
            } 
            return $sql;
        }
	}
        
	/**********************************************************************
    * desc: does an replace query with an array
    * param: @table, - database table to access
    *		@keyandvalue - table fields, assoc array with key = value (doesn't need escaped)
    * returns: id of replaced record, or false for error
	*/
    function replace($table='', $keyandvalue) {
            return $this->_query_insert_replace($table, $keyandvalue, 'REPLACE');
        }

	/**********************************************************************
    * desc: does an insert query with an array
    * param: @table, - database table to access
    * 		@keyandvalue - table fields, assoc array with key = value (doesn't need escaped)
    * returns: id of inserted record, or false for error
	*/
    function insert($table='', $keyandvalue) {
        return $this->_query_insert_replace($table, $keyandvalue, 'INSERT');
    }
    
	/**********************************************************************
    * desc: does an insert into select statement by calling insert method helper then selecting method
    * param: @totable, - database table to insert table into 
    *		@tocolumns - the receiving columns from other table columns, leave blank for all or array of column fields
    *        @wherekey, - where clause ( array(x, =, y, and, extra) ) or ( "x = y and extra" )
    *		example: where( array(key, operator, value, combine, extra) ); or where( "key operator value combine extra" );
    * returns: 
	*/
    function insert_select($totable='', $tocolumns='*', $fromtable, $fromcolumns='*', ...$fromwhere) {
        $puttotable = $this->_query_insert_replace($totable, $tocolumns, 'INSERT', false);
        $getfromtable = $this->select_sql($fromtable, $fromcolumns, ...$fromwhere);
        if (is_string($puttotable) && is_string($getfromtable))
            return (($this->getPrepare()) && !empty($this->getParamaters())) ? $this->query($puttotable." ".$getfromtable, true) : $this->query($puttotable." ".$getfromtable) ;
        else {
			$this->setParamaters();
            return false;          			
		}                 
    }    
    
}
