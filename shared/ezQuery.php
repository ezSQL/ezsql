<?php

use ezsql\ezSchema;
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

    public static function clean($string) 
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
    
    public function isPrepareActive() 
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

    /**
    * Convert array to string, and attach '`, `' for separation, if none is provided.
    *
    * @return string
    */  
    private function to_string($arrays, $separation = ',' )  
    {        
        if (is_array( $arrays )) {
            $columns = '';
            foreach($arrays as $val) {
                $columns .= $val.$separation.' ';
            }
            $columns = rtrim($columns, $separation.' ');
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

    public function innerJoin(
        string $leftTable = null, 
        string $rightTable = null, 
        string $leftColumn = null, string $rightColumn = null, $condition = \EQ)
    {
        return $this->joining(
            'INNER', $leftTable, $rightTable, $leftColumn, $rightColumn, $condition);
    }

    public function leftJoin(
        string $leftTable = null, 
        string $rightTable = null, 
        string $leftColumn = null, string $rightColumn = null, $condition = \EQ)
    {
        return $this->joining(
            'LEFT', $leftTable, $rightTable, $leftColumn, $rightColumn, $condition);
    }

    public function rightJoin(
        string $leftTable = null, 
        string $rightTable = null, 
        string $leftColumn = null, string $rightColumn = null, $condition = \EQ)
    {
        return $this->joining(
            'RIGHT', $leftTable, $rightTable, $leftColumn, $rightColumn, $condition);
    }

    public function fullJoin(
        string $leftTable = null, 
        string $rightTable = null, 
        string $leftColumn = null, string $rightColumn = null, $condition = \EQ)
    {
        return $this->joining(
            'FULL', $leftTable, $rightTable, $$leftColumn, $rightColumn, $condition);
    }

    /**
    * For multiple select joins, combine rows from tables where `on` condition is met
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
    * @param string $type - Either `INNER`, `LEFT`, `RIGHT`, `FULL`
    * @param string $leftTable - 
    * @param string $rightTable - 
    *
    * @param string $leftColumn - 
    * @param string $rightColumn - 
    *
    * @param string $condition -  
    *
    * @return bool|string JOIN sql statement, false for error
    */
    private function joining(
        String $type = \_INNER,
        string $leftTable = null, 
        string $rightTable = null, 
        string $leftColumn = null, string $rightColumn = null, $condition = \EQ) 
    {
        if (!in_array($type, \_JOINERS) 
            || !in_array($condition, \_BOOLEAN) 
            || empty($leftTable) 
            || empty($rightTable) || empty($columnFields) || empty($leftColumn)
        ) {
            return false;
        }

        if (\is_string($leftColumn) && empty($rightColumn))
            $onCondition = ' ON '.$leftTable.$leftColumn.' = '.$rightTable.$leftColumn;
        elseif ($condition !== \EQ)
            $onCondition = ' ON '.$leftTable.$leftColumn." $condition ".$rightTable.$rightColumn;
        else
            $onCondition = ' ON '.$leftTable.$leftColumn.' = '.$rightTable.$rightColumn;

        return ' '.$type.' JOIN '.$rightTable.$onCondition;
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
			if (\is_string($whereKeyArray[0])) {
                if ((\strpos($whereKeyArray[0], 'WHERE') !== false) 
                    || (\strpos($whereKeyArray[0], 'HAVING') !== false)
                )
                    return $whereKeyArray[0];
				foreach ($whereKeyArray as $makeArray) 
					$WhereKeys[] = \explode('  ', $makeArray);	
			} else 
				$WhereKeys = $whereKeyArray;			
		} else 
			return '';
		
		foreach ($WhereKeys as $values) {
			$operator[] = (isset($values[1])) ? $values[1]: '';
			if (!empty($values[1])){
				if (\strtoupper($values[1]) == 'IN') {
					$WhereKey[ $values[0] ] = \array_slice((array) $values, 2);
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
                $isCondition = \strtoupper($operator[$i]);
				$combine = $combiner[$i];
				if ( \in_array(\strtoupper($combine), \_COMBINERS) || isset($extra[$i])) 
					$combineWith = (isset($extra[$i])) ? $combine : \strtoupper($combine);
				else 
                    $combineWith = _AND;

                if (! \in_array( $isCondition, \_BOOLEAN_OPERATORS)) {
                    return $this->clearParameters();
                } else {
                    if (($isCondition == \_BETWEEN) || ($isCondition == \_notBETWEEN)) {
						$value = $this->escape($combineWith);
						if (\in_array(\strtoupper($extra[$i]), \_COMBINERS)) 
							$myCombineWith = \strtoupper($extra[$i]);
						else 
                            $myCombineWith = _AND;

						if ($this->isPrepareActive()) {
							$where .= "$key ".$isCondition.' '._TAG." AND "._TAG." $myCombineWith ";
							$this->setParameters($val);
							$this->setParameters($combineWith);
						} else 
                            $where .= "$key ".$isCondition." '".$this->escape($val)."' AND '".$value."' $myCombineWith ";
                            
						$combineWith = $myCombineWith;
					} elseif ($isCondition == \_IN) {
						$value = '';
						foreach ($val as $inValues) {
							if ($this->isPrepareActive()) {
								$value .= _TAG.', ';
								$this->setParameters($inValues);
							} else 
								$value .= "'".$this->escape($inValues)."', ";
                        }                        
						$where .= "$key ".$isCondition." ( ".\rtrim($value, ', ')." ) $combineWith ";
					} elseif (((\strtolower($val) == 'null') || ($isCondition == 'IS') || ($isCondition == 'IS NOT'))) {
                        $isCondition = (($isCondition == 'IS') || ($isCondition == 'IS NOT')) ? $isCondition : 'IS';
                        $where .= "$key ".$isCondition." NULL $combineWith ";
                    } elseif ((($isCondition == \_LIKE) || ($isCondition == \_notLIKE)) && ! \preg_match('/[_%?]/', $val)) {
                        return $this->clearParameters();
                    } else {
						if ($this->isPrepareActive()) {
							$where .= "$key ".$isCondition.' '._TAG." $combineWith ";
							$this->setParameters($val);
						} else 
							$where .= "$key ".$isCondition." '".$this->escape($val)."' $combineWith ";
                    }
                    
                    $i++;
                }
            }
            $where = \rtrim($where, " $combineWith ");
        }
		
        if (($this->isPrepareActive()) && !empty($this->getParameters()) && ($where != '1'))
			return " $whereOrHaving ".$where.' ';
        
		return ($where != '1') ? " $whereOrHaving ".$where.' ' : ' ' ;
    }        
    
    public function selecting($table ='', $columnFields = '*', ...$conditions) 
    {    
		$getFromTable = $this->fromTable;
		$getSelect_result = $this->select_result;       
		$getIsInto = $this->isInto;
        
		$this->fromTable = null;
		$this->select_result = true;	
		$this->isInto = false;	
        
        $skipWhere = false;
        $WhereKeys = $conditions;
        $where = '';
		
        if (empty($table)) {
            return $this->clearParameters();
        }
        
        $columns = $this->to_string($columnFields);
        
		if (isset($getFromTable) && ! $getIsInto) 
			$sql="CREATE TABLE $table AS SELECT $columns FROM ".$getFromTable;
        elseif (isset($getFromTable) && $getIsInto) 
			$sql="SELECT $columns INTO $table FROM ".$getFromTable;
        else 
			$sql="SELECT $columns FROM ".$table;

        if (!empty($conditions)) {
			if (\is_string($conditions[0])) {
                $args_by = '';
                $joinSet = false;      
                $groupBySet = false;      
                $havingSet = false;             
                $orderBySet = false;   
                $limitSet = false;     
                $unionSet = false;
				foreach ($conditions as $checkFor) {
                    if (\strpos($checkFor, 'JOIN') !== false ) {
                        $args_by .= $checkFor;
                        $joinSet = true;
                    } elseif (\strpos($checkFor, 'WHERE') !== false ) {
                        $args_by .= $checkFor;
                        $skipWhere = true;
                    } elseif (\strpos($checkFor, 'GROUP BY') !== false ) {
                        $args_by .= ' '.$checkFor;
                        $groupBySet = true;
                    } elseif (\strpos($checkFor, 'HAVING') !== false ) {
                        if ($groupBySet) {
                            $args_by .= ' '.$checkFor;
                            $havingSet = true;
                        } else {
                            return $this->clearParameters();
                        }
                    } elseif (\strpos($checkFor, 'ORDER BY') !== false ) {
                        $args_by .= ' '.$checkFor;    
                        $orderBySet = true;
                    } elseif (\strpos($checkFor, 'LIMIT') !== false ) {
                        $args_by .= ' '.$checkFor;    
                        $limitSet = true;
                    } elseif (\strpos($checkFor, 'UNION') !== false ) {
                        $args_by .= ' '.$checkFor;    
                        $unionSet = true;
                    }
                }

                if ($joinSet || $skipWhere || $groupBySet || $havingSet || $orderBySet || $limitSet || $unionSet) {
                    $where = $args_by;
                    $skipWhere = true;
                }
			}		
		} else {
            $skipWhere = true;
        }        
        
        if (! $skipWhere)
            $where = $this->where( ...$WhereKeys);
        
        if (\is_string($where)) {
            $sql .= $where;
            if ($getSelect_result) 
                return (($this->isPrepareActive()) && !empty($this->getParameters())) 
                    ? $this->get_results($sql, OBJECT, true) 
                    : $this->get_results($sql);     
            return $sql;
        }
        
        return $this->clearParameters();
    }

    /**
     * Get SQL statement string from selecting method instead of executing get_result
     * @return string
     */
    private function select_sql($table = '', $columnFields = '*', ...$conditions)
    {
		$this->select_result = false;
        return $this->selecting($table, $columnFields, ...$conditions);	            
    }

    public function union($table = '', $columnFields = '*', ...$conditions)
    {
        return 'UNION '.$this->select_sql($table, $columnFields, ...$conditions);           
    }

    public function unionAll($table = '', $columnFields = '*', ...$conditions)
    {
        return 'UNION ALL '.$this->select_sql($table, $columnFields, ...$conditions);             
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
            return (($this->isPrepareActive()) && !empty($this->getParameters())) 
                ? $this->query($newTableFromTable, true) 
                : $this->query($newTableFromTable); 

        return $this->clearParameters();   
    }
    
    public function select_into($newTable, $fromColumns, $oldTable = null, ...$fromWhere) 
    {
		$this->isInto = true;        
		if (isset($oldTable))
			$this->fromTable = $oldTable;
		else
			return $this->clearParameters();
			
        $newTableFromTable = $this->select_sql($newTable, $fromColumns, ...$fromWhere);
        if (is_string($newTableFromTable))
            return (($this->isPrepareActive()) && !empty($this->getParameters())) 
                ? $this->query($newTableFromTable, true) 
                : $this->query($newTableFromTable); 
        
        return $this->clearParameters();     
    }

    public function update($table = '', $keyAndValue, ...$WhereKeys) 
    {        
        if ( ! is_array( $keyAndValue ) || empty($table) ) {
			return $this->clearParameters();
        }
        
        $sql = "UPDATE $table SET ";
        
        foreach($keyAndValue as $key => $val) {
            if(\strtolower($val)=='null') {
				$sql .= "$key = NULL, ";
            } elseif(\in_array(\strtolower($val), array( 'current_timestamp()', 'date()', 'now()' ))) {
				$sql .= "$key = CURRENT_TIMESTAMP(), ";
			} else {
				if ($this->isPrepareActive()) {
					$sql .= "$key = "._TAG.", ";
					$this->setParameters($val);
				} else 
					$sql .= "$key = '".$this->escape($val)."', ";
			}
        }
        
        $where = $this->where(...$WhereKeys);
        if (\is_string($where)) {   
            $sql = \rtrim($sql, ', ') . $where;
            return (($this->isPrepareActive()) && !empty($this->getParameters())) 
                ? $this->query($sql, true) 
                : $this->query($sql) ;       
        } 
        
        return $this->clearParameters();
    }   
         
    public function delete($table = '', ...$WhereKeys) 
    {   
        if ( empty($table) ) {
			return $this->clearParameters();         			
		}  
		
        $sql = "DELETE FROM $table";
        
        $where = $this->where(...$WhereKeys);
        if (\is_string($where)) {   
            $sql .= $where;						
            return (($this->isPrepareActive()) && !empty($this->getParameters())) 
                ? $this->query($sql, true) 
                : $this->query($sql);  
        }

        return $this->clearParameters();       
    }

	/**
    * Helper does the actual insert or replace query with an array
	* @return mixed bool/results - false for error
	*/
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
                if (\strtolower($val) == 'null') 
                    $value .= "NULL, ";
                elseif (\in_array(\strtolower($val), array( 'current_timestamp()', 'date()', 'now()' ))) 
                    $value .= "CURRENT_TIMESTAMP(), ";
                else {
					if ($this->isPrepareActive()) {
						$value .= _TAG.", ";
						$this->setParameters($val);
					} else 
						$value .= "'".$this->escape($val)."', ";
				}               
            }
            
            $sql .= "(". rtrim($index, ', ') .") VALUES (". rtrim($value, ', ') .");";

			if (($this->isPrepareActive()) && !empty($this->getParameters())) 
				$ok = $this->query($sql, true);
			else 
				$ok = $this->query($sql);
				
            if ($ok)
                return $this->insert_id;

            return $this->clearParameters();
        } else {
            if (\is_array($keyAndValue)) {
                if (\array_keys($keyAndValue) === \range(0, \count($keyAndValue) - 1)) {
                    foreach($keyAndValue as $key) {
                        $index .= "$key, ";                
                    }
                    $sql .= " (". \rtrim($index, ', ') .") ";                         
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

        if (\is_string($putToTable) && \is_string($getFromTable))
            return (($this->isPrepareActive()) && !empty($this->getParameters())) 
                ? $this->query($putToTable." ".$getFromTable, true) 
                : $this->query($putToTable." ".$getFromTable) ;

		return $this->clearParameters();      
    }

    private function schema(array ...$columnDataOptions) 
    {
        if (empty($columnDataOptions))
            return false;

        $columnData = '';
        foreach($columnDataOptions as $datatype) {
            $column = \array_shift($datatype);
            $type = \array_shift($datatype);
            $data =  \datatype($type, $datatype);
            if (!empty($data))
                $columnData .= $column.' '.$data.', ';
        }

        $schemaColumns = !empty($columnData) ? \rtrim($columnData, ', ') : null;
        if (\is_string($schemaColumns))
            return $schemaColumns;

        return false;
    }

    public function create(string $table = null, ...$schemas) 
    {
        $vendor = ezSchema::vendor();
        if (empty($table) || empty($schemas) || empty($vendor))
            return false;

        $sql = 'CREATE TABLE '.$table.' ( ';

        $skipSchema = false;
        if (! \is_array($schemas[0])) {
            $data = '';
            $allowedTypes = ezSchema::STRINGS['shared'];
            $allowedTypes += ezSchema::STRINGS[$vendor];
            $allowedTypes += ezSchema::NUMERICS['shared'];
            $allowedTypes += ezSchema::NUMERICS[$vendor];
            $allowedTypes += ezSchema::DATE_TIME['shared'];
            $allowedTypes += ezSchema::DATE_TIME[$vendor];
            $allowedTypes += ezSchema::OBJECTS[$vendor];
            $pattern = "/".\implode('|', $allowedTypes)."/i";
            foreach($schemas as $types) {
                if (\is_string($types)) {
                    if (\preg_match($pattern, $types)) {
                        $data .= (\strpos($types, ', ') !== false) ? $types : $types.', ';
                        $skipSchema = true;
                    }
                }
            }
            $schema = $skipSchema ? \rtrim($data, ', ') : $data;
        }

        if (! $skipSchema) {
            $schema = $this->schema( ...$schemas);
        }

        $createTable = !empty($schema) ? $sql.$schema.' );' : null;
        if (\is_string($createTable))
            return $this->query($createTable);

        return false;
    }
}
