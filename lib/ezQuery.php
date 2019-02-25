<?php

namespace ezsql;

use ezsql\ezQueryInterface;

class ezQuery implements ezQueryInterface
{ 		
	protected $select_result = true;
	protected $prepareActive = false;
    
	private $fromTable = null;
    private $isWhere = true;    
    private $isInto = false;
    
    public function __construct() 
    {
    }

    public function clean($string) 
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
    
    public function getPrepare() 
    {
        return $this->prepareActive;
	}
  	
    public function setPrepare($on = true) 
    {
        $this->prepareActive = ($on) ? true : false;
	}  	
    
    public function getParameters() 
    {
		return $this->preparedValues;
	}
    
    public function setParameters($valueToAdd = null) 
    {
        return array_push($this->preparedValues, $valueToAdd); 
    }
    
    public function clearParameters() 
    {
        $this->preparedValues = array();
        return false;
	}
    
    public function to_string($arrays)  
    {        
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

    public function groupBy($groupBy)
    {
        if (empty($groupBy)) {
            return false;
        }
        
        $columns = $this->to_string($groupBy);
        
        return 'GROUP BY ' .$columns;
    }

    public function having(...$having)
    {
        $this->isWhere = false;
        return $this->where( ...$having);
    }
 
    public function orderBy($orderBy, $order)
    {
        if (empty($orderBy)) {
            return false;
        }
        
        $columns = $this->to_string($orderBy);
        
        $order = (in_array(strtoupper($order), array( 'ASC', 'DESC'))) ? strtoupper($order) : 'ASC';
        
        return 'ORDER BY '.$columns.' '. $order;
    }

    public function limit($numberOf, $offset = null)
    {
        if (empty($numberOf)) {
            return false;
        }
        
        $rows = (int) $numberOf;
        
        $value = !empty($offset) ? ' OFFSET '.(int) $offset : '';
        
        return 'LIMIT '.$rows.$value;
    }

    public function where( ...$whereKeyArray) 
    {      
        $whereOrHaving = ($this->isWhere) ? 'WHERE' : 'HAVING';
        $this->isWhere = true;
        
		if (!empty($whereKeyArray)) {
			if (is_string($whereKeyArray[0])) {
                if ((strpos($whereKeyArray[0], 'WHERE') !== false) || (strpos($whereKeyArray[0], 'HAVING') !== false))
                    return $whereKeyArray[0];
				foreach ($whereKeyArray as $makeArray) 
					$WhereKeys[] = explode('  ', $makeArray);	
			} else 
				$WhereKeys = $whereKeyArray;			
		} else 
			return '';
		
		foreach ($WhereKeys as $values) {
			$operator[] = (isset($values[1])) ? $values[1]: '';
			if (!empty($values[1])){
				if (strtoupper($values[1]) == 'IN') {
					$WhereKey[ $values[0] ] = array_slice((array) $values, 2);
					$combiner[] = (isset($values[3])) ? $values[3]: _AND;
					$extra[] = (isset($values[4])) ? $values[4]: null;				
				} else {
					$WhereKey[ (isset($values[0])) ? $values[0] : '1' ] = (isset($values[2])) ? $values[2] : '' ;
					$combiner[] = (isset($values[3])) ? $values[3]: _AND;
					$extra[] = (isset($values[4])) ? $values[4]: null;
				}				
			} else {
                return $this->clearParameters();
            }                
		}
        
        $where = '1';    
        if (! isset($WhereKey['1'])) {
            $where = '';
            $i = 0;
            foreach($WhereKey as $key => $val) {
                $isCondition = strtoupper($operator[$i]);
				$combine = $combiner[$i];
				if ( in_array(strtoupper($combine), array( 'AND', 'OR', 'NOT', 'AND NOT' )) || isset($extra[$i])) 
					$combineWith = (isset($extra[$i])) ? $combine : strtoupper($combine);
				else 
                    $combineWith = _AND;

                if (! in_array( $isCondition, array( '<', '>', '=', '!=', '>=', '<=', '<>', 'IN', 'LIKE', 'NOT LIKE', 'BETWEEN', 'NOT BETWEEN', 'IS', 'IS NOT' ) )) {
                    return $this->clearParameters();
                } else {
                    if (($isCondition == 'BETWEEN') || ($isCondition == 'NOT BETWEEN')) {
						$value = $this->escape($combineWith);
						if (in_array(strtoupper($extra[$i]), array( 'AND', 'OR', 'NOT', 'AND NOT' ))) 
							$myCombineWith = strtoupper($extra[$i]);
						else 
                            $myCombineWith = _AND;

						if ($this->getPrepare()) {
							$where .= "$key ".$isCondition.' '._TAG." AND "._TAG." $myCombineWith ";
							$this->setParameters($val);
							$this->setParameters($combineWith);
						} else 
                            $where .= "$key ".$isCondition." '".$this->escape($val)."' AND '".$value."' $myCombineWith ";
                            
						$combineWith = $myCombineWith;
					} elseif ($isCondition == 'IN') {
						$value = '';
						foreach ($val as $inValues) {
							if ($this->getPrepare()) {
								$value .= _TAG.', ';
								$this->setParameters($inValues);
							} else 
								$value .= "'".$this->escape($inValues)."', ";
                        }                        
						$where .= "$key ".$isCondition." ( ".rtrim($value, ', ')." ) $combineWith ";
					} elseif (((strtolower($val) == 'null') || ($isCondition == 'IS') || ($isCondition == 'IS NOT'))) {
                        $isCondition = (($isCondition == 'IS') || ($isCondition == 'IS NOT')) ? $isCondition : 'IS';
                        $where .= "$key ".$isCondition." NULL $combineWith ";
                    } elseif ((($isCondition == 'LIKE') || ($isCondition == 'NOT LIKE')) && ! preg_match('/[_%?]/', $val)) {
                        return $this->clearParameters();
                    } else {
						if ($this->getPrepare()) {
							$where .= "$key ".$isCondition.' '._TAG." $combineWith ";
							$this->setParameters($val);
						} else 
							$where .= "$key ".$isCondition." '".$this->escape($val)."' $combineWith ";
                    }
                    
                    $i++;
                }
            }
            $where = rtrim($where, " $combineWith ");
        }
		
        if (($this->getPrepare()) && !empty($this->getParameters()) && ($where != '1'))
			return " $whereOrHaving ".$where.' ';
		else
			return ($where != '1') ? " $whereOrHaving ".$where.' ' : ' ' ;
    }        
    
    public function selecting($table ='', $fields ='*', ...$get_args) 
    {    
		$getFromTable = $this->fromTable;
		$getSelect_result = $this->select_result;       
		$getIsInto = $this->isInto;
        
		$this->fromTable = null;
		$this->select_result = true;	
		$this->isInto = false;	
        
        $skipWhere = false;
        $WhereKeys = $get_args;
        $where = '';
		
        if (empty($table)) {
            return $this->clearParameters();
        }
        
        $columns = $this->to_string($fields);
        
		if (isset($getFromTable) && ! $getIsInto) 
			$sql = "CREATE TABLE $table AS SELECT $columns FROM ".$getFromTable;
        elseif (isset($getFromTable) && $getIsInto) 
			$sql = "SELECT $columns INTO $table FROM ".$getFromTable;
        else 
			$sql = "SELECT $columns FROM ".$table;

        if (!empty($get_args)) {
			if (is_string($get_args[0])) {
                $args_by = '';
                $groupBySet = false;      
                $havingSet = false;             
                $orderBySet = false;   
                $limitSet = false;   
				foreach ($get_args as $where_groupBy_having_orderby_limit) {
                    if (strpos($where_groupBy_having_orderby_limit, 'WHERE') !== false ) {
                        $args_by .= $where_groupBy_having_orderby_limit;
                        $skipWhere = true;
                    } elseif (strpos($where_groupBy_having_orderby_limit, 'GROUP BY') !== false ) {
                        $args_by .= ' '.$where_groupBy_having_orderby_limit;
                        $groupBySet = true;
                    } elseif (strpos($where_groupBy_having_orderby_limit, 'HAVING') !== false ) {
                        if ($groupBySet) {
                            $args_by .= ' '.$where_groupBy_having_orderby_limit;
                            $havingSet = true;
                        } else {
                            return $this->clearParameters();
                        }
                    } elseif (strpos($where_groupBy_having_orderby_limit, 'ORDER BY') !== false ) {
                        $args_by .= ' '.$where_groupBy_having_orderby_limit;    
                        $orderBySet = true;
                    } elseif (strpos($where_groupBy_having_orderby_limit, 'LIMIT') !== false ) {
                        $args_by .= ' '.$where_groupBy_having_orderby_limit;    
                        $limitSet = true;
                    }
                }
                if ($skipWhere || $groupBySet || $havingSet || $orderBySet || $limitSet) {
                    $where = $args_by;
                    $skipWhere = true;
                }
			}		
		} else {
            $skipWhere = true;
        }        
        
        if (! $skipWhere)
            $where = $this->where( ...$WhereKeys);
        
        if (is_string($where)) {
            $sql .= $where;
            if ($getSelect_result) 
                return (($this->getPrepare()) && !empty($this->getParameters())) 
                    ? $this->get_results($sql, OBJECT, true) 
                    : $this->get_results($sql);     
            else 
                return $sql;
        } else {
            return $this->clearParameters();
        }             
    }
	
    public function select_sql($table = '', $fields = '*', ...$get_args) 
    {
		$this->select_result = false;
        return $this->selecting($table, $fields, ...$get_args);	            
    }
   
    public function create_select($newTable, $fromColumns, $oldTable = null, ...$fromWhere) 
    {
		if (isset($oldTable))
			$this->fromTable = $oldTable;
		else {
            return $this->clearParameters();            
        }
			
        $newTableFromTable = $this->select_sql($newTable, $fromColumns, ...$fromWhere);			
        if (is_string($newTableFromTable))
            return (($this->getPrepare()) && !empty($this->getParameters())) 
                ? $this->query($newTableFromTable, true) 
                : $this->query($newTableFromTable); 
        else {
            return $this->clearParameters();   		
        }
    }
    
    public function select_into($newTable, $fromColumns, $oldTable = null, ...$fromWhere) 
    {
		$this->isInto = true;        
		if (isset($oldTable))
			$this->fromTable = $oldTable;
		else {
			return $this->clearParameters();          			
		}  
			
        $newTableFromTable = $this->select_sql($newTable, $fromColumns, ...$fromWhere);
        if (is_string($newTableFromTable))
            return (($this->getPrepare()) && !empty($this->getParameters())) 
                ? $this->query($newTableFromTable, true) 
                : $this->query($newTableFromTable); 
        else {
			return $this->clearParameters();         			
		}  
    }

    public function update($table = '', $keyAndValue, ...$WhereKeys) 
    {        
        if ( ! is_array( $keyAndValue ) || empty($table) ) {
			return $this->clearParameters();
        }
        
        $sql = "UPDATE $table SET ";
        
        foreach($keyAndValue as $key => $val) {
            if(strtolower($val) == 'null') {
				$sql .= "$key = NULL, ";
            } elseif(in_array(strtolower($val), array( 'current_timestamp()', 'date()', 'now()' ))) {
				$sql .= "$key = CURRENT_TIMESTAMP(), ";
			} else {
				if ($this->getPrepare()) {
					$sql .= "$key = "._TAG.", ";
					$this->setParameters($val);
				} else 
					$sql .= "$key = '".$this->escape($val)."', ";
			}
        }
        
        $where = $this->where(...$WhereKeys);
        if (is_string($where)) {   
            $sql = rtrim($sql, ', ') . $where;
            return (($this->getPrepare()) && !empty($this->getParameters())) 
                ? $this->query($sql, true) 
                : $this->query($sql);       
        } else {
			return $this->clearParameters();
		}
    }   
         
    public function delete($table = '', ...$WhereKeys) 
    {   
        if ( empty($table) ) {
			return $this->clearParameters();         			
		}  
		
        $sql = "DELETE FROM $table";
        
        $where = $this->where(...$WhereKeys);
        if (is_string($where)) {   
            $sql .= $where;						
            return (($this->getPrepare()) && !empty($this->getParameters())) 
                ? $this->query($sql, true) 
                : $this->query($sql);  
        } else {
			return $this->clearParameters();         			
		}  
    }

    private function _query_insert_replace($table = '', $keyAndValue, $type = '', $execute = true) 
    {  
        if ((! is_array($keyAndValue) && ($execute)) || empty($table)) {
			return $this->clearParameters();          			
		}  
        
        if ( ! in_array( strtoupper( $type ), array( 'REPLACE', 'INSERT' ))) {
			return $this->clearParameters();          			
		}  
            
        $sql = "$type INTO $table";
        $value = ''; 
        $index = '';

        if ($execute) {
            foreach($keyAndValue as $key => $val) {
                $index .= "$key, ";
                if (strtolower($val) == 'null') 
                    $value .= "NULL, ";
                elseif (in_array(strtolower($val), array( 'current_timestamp()', 'date()', 'now()' ))) 
                    $value .= "CURRENT_TIMESTAMP(), ";
                else {
					if ($this->getPrepare()) {
						$value .= _TAG.", ";
						$this->setParameters($val);
					} else 
						$value .= "'".$this->escape($val)."', ";
				}               
            }
            
            $sql .= "(". rtrim($index, ', ') .") VALUES (". rtrim($value, ', ') .");";

			if (($this->getPrepare()) && !empty($this->getParameters())) 
				$ok = $this->query($sql, true);
			else 
				$ok = $this->query($sql);
				
            if ($ok)
                return $this->insert_id;
            else {
				return $this->clearParameters();          			
			}  
        } else {
            if (is_array($keyAndValue)) {
                if (array_keys($keyAndValue) === range(0, count($keyAndValue) - 1)) {
                    foreach($keyAndValue as $key) {
                        $index .= "$key, ";                
                    }
                    $sql .= " (". rtrim($index, ', ') .") ";                         
                } else {
					return false;          			
				}          
            } 
            return $sql;
        }
	}
        
    public function replace($table = '', $keyAndValue) 
    {
            return $this->_query_insert_replace($table, $keyAndValue, 'REPLACE');
        }

    public function insert($table = '', $keyAndValue) 
    {
        return $this->_query_insert_replace($table, $keyAndValue, 'INSERT');
    }

    public function insert_select($toTable = '', $toColumns = '*', $fromTable = null, $fromColumns = '*', ...$fromWhere) 
    {
        $putToTable = $this->_query_insert_replace($toTable, $toColumns, 'INSERT', false);
        $getFromTable = $this->select_sql($fromTable, $fromColumns, ...$fromWhere);

        if (is_string($putToTable) && is_string($getFromTable))
            return (($this->getPrepare()) && !empty($this->getParameters())) 
                ? $this->query($putToTable." ".$getFromTable, true) 
                : $this->query($putToTable." ".$getFromTable) ;
        else {
			return $this->clearParameters();          			
		}
    }    
}
