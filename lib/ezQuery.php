<?php

namespace ezsql;

use ezsql\ezSchema;
use ezsql\ezQueryInterface;
use function ezsql\functions\{column, get_vendor};

class ezQuery implements ezQueryInterface
{
    protected $select_result = true;
    protected $prepareActive = false;
    protected $preparedValues = array();

    /**
     * ID generated from the AUTO_INCREMENT of the previous INSERT operation (if any)
     * @var int
     */
    protected $insertId = null;

    /**
     * The table `name` to use on calls to `ing` ending
     * `CRUD` methods/functions.
     *
     * @var string
     */
    protected $table = '';

    /**
     * A `prefix` to append to `table` on calls to `ing` ending
     * `CRUD` methods/functions.
     *
     * @var string
     */
    protected $prefix = '';

    private $fromTable = null;
    private $isWhere = true;
    private $isInto = false;
    private $whereSQL = null;
    private $combineWith = null;

    public function __construct()
    {
    }

    /**
     * Return status of prepare function availability in shortcut method calls
     */
    protected function isPrepareOn()
    {
        return $this->prepareActive;
    }

    /**
     * Returns array of parameter values for prepare function
     * @return array
     */
    protected function prepareValues()
    {
        return $this->preparedValues;
    }

    /**
     * Add values to array variable for prepare function.
     * @param mixed $value
     *
     * @return int array count
     */
    protected function addPrepare($value = null)
    {
        return \array_push($this->preparedValues, $value);
    }

    /**
     * Clear out prepare parameter values
     *
     * @return bool false
     */
    protected function clearPrepare()
    {
        $this->preparedValues = array();
        return false;
    }

    public function prepareOn()
    {
        $this->prepareActive = true;
    }

    public function prepareOff()
    {
        $this->prepareActive = $this->clearPrepare();
    }

    /**
     * Convert array to string, and attach '`,`' for separation, if none is provided.
     *
     * @return string
     */
    public static function to_string($arrays, $separation = ',')
    {
        if (\is_array($arrays)) {
            $columns = '';
            foreach ($arrays as $val) {
                $columns .= $val . $separation . ' ';
            }
            $columns = \rtrim($columns, $separation . ' ');
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

        return 'GROUP BY ' . $columns;
    }

    public function having(...$conditions)
    {
        $this->isWhere = false;
        return $this->where(...$conditions);
    }

    public function innerJoin(
        string $leftTable = null,
        string $rightTable = null,
        string $leftColumn = null,
        string $rightColumn = null,
        string $tableAs = null,
        $condition = \EQ
    ) {
        return $this->joining(
            'INNER',
            $leftTable,
            $rightTable,
            $leftColumn,
            $rightColumn,
            $tableAs,
            $condition
        );
    }

    public function leftJoin(
        string $leftTable = null,
        string $rightTable = null,
        string $leftColumn = null,
        string $rightColumn = null,
        string $tableAs = null,
        $condition = \EQ
    ) {
        return $this->joining(
            'LEFT',
            $leftTable,
            $rightTable,
            $leftColumn,
            $rightColumn,
            $tableAs,
            $condition
        );
    }

    public function rightJoin(
        string $leftTable = null,
        string $rightTable = null,
        string $leftColumn = null,
        string $rightColumn = null,
        string $tableAs = null,
        $condition = \EQ
    ) {
        return $this->joining(
            'RIGHT',
            $leftTable,
            $rightTable,
            $leftColumn,
            $rightColumn,
            $tableAs,
            $condition
        );
    }

    public function fullJoin(
        string $leftTable = null,
        string $rightTable = null,
        string $leftColumn = null,
        string $rightColumn = null,
        string $tableAs = null,
        $condition = \EQ
    ) {
        return $this->joining(
            'FULL',
            $leftTable,
            $rightTable,
            $leftColumn,
            $rightColumn,
            $tableAs,
            $condition
        );
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
     * @param string $tableAs -
     *
     * @param string $condition -
     *
     * @return bool|string JOIN sql statement, false for error
     */
    private function joining(
        String $type = \_INNER,
        string $leftTable = null,
        string $rightTable = null,
        string $leftColumn = null,
        string $rightColumn = null,
        string $tableAs = null,
        $condition = \EQ
    ) {
        if (
            !\in_array($type, \_JOINERS)
            || !\in_array($condition, \_BOOLEAN)
            || empty($leftTable)
            || empty($rightTable)
            || empty($leftColumn)
        ) {
            return false;
        }

        if (empty($tableAs))
            $tableAs = $rightTable;

        if (\is_string($leftColumn) && empty($rightColumn))
            $onCondition = ' ON ' . $leftTable . '.' . $leftColumn . ' = ' . $tableAs . '.' . $leftColumn;
        elseif ($condition !== \EQ)
            $onCondition = ' ON ' . $leftTable . '.' . $leftColumn . ' ' . $condition . ' ' . $tableAs . '.' . $rightColumn;
        else
            $onCondition = ' ON ' . $leftTable . '.' . $leftColumn . ' = ' . $tableAs . '.' . $rightColumn;

        return ' ' . $type . ' JOIN ' . $rightTable . ' AS ' . $tableAs . ' ' . $onCondition;
    }

    public function orderBy($orderBy, $order)
    {
        if (empty($orderBy)) {
            return false;
        }

        $columns = $this->to_string($orderBy);

        $order = (\in_array(\strtoupper($order), array('ASC', 'DESC'))) ? \strtoupper($order) : 'ASC';

        return 'ORDER BY ' . $columns . ' ' . $order;
    }

    public function limit($numberOf, $offset = null)
    {
        if (empty($numberOf)) {
            return false;
        }

        $rows = (int) $numberOf;

        $value = !empty($offset) ? ' OFFSET ' . (int) $offset : '';

        return 'LIMIT ' . $rows . $value;
    }

    private function conditions($key, $condition, $value, $combine, $extra)
    {
        $groupStart = (!empty($extra) && $extra === '(') ? $extra : '';
        $groupEnd = (!empty($extra) && $extra === ')') ? $extra : '';

        if ($this->isPrepareOn()) {
            $this->whereSQL .= "$groupStart $key $condition " . \_TAG . " $groupEnd $combine ";
            $this->addPrepare($value);
        } else
            $this->whereSQL .= "$groupStart $key $condition '" . $this->escape($value) . "' $groupEnd $combine ";
    }

    private function conditionBetween($key, $condition, $valueOne, $valueTwo, $combine)
    {
        $_valueTwo = $this->escape($valueTwo);
        $andCombineWith = \_AND;
        if (\in_array(\strtoupper($combine), \_COMBINERS))
            $andCombineWith = \strtoupper($combine);

        if ($this->isPrepareOn()) {
            $this->whereSQL .= "$key " . $condition . ' ' . \_TAG . " AND " . \_TAG . " $andCombineWith ";
            $this->addPrepare($valueOne);
            $this->addPrepare($valueTwo);
        } else
            $this->whereSQL .= "$key $condition '" . $this->escape($valueOne) . "' AND '" . $_valueTwo . "' $andCombineWith ";

        $this->combineWith = $andCombineWith;
    }

    private function conditionIn($key, $condition, $valueRow, $combine)
    {
        $value = '';
        foreach ($valueRow as $splitValue) {
            if ($this->isPrepareOn()) {
                $value .= \_TAG . ', ';
                $this->addPrepare($splitValue);
            } else
                $value .= "'" . $this->escape($splitValue) . "', ";
        }

        $this->whereSQL .= "$key $condition ( " . \rtrim($value, ', ') . " ) $combine ";
    }

    private function conditionIs($key, $condition, $combine)
    {
        $isCondition = (($condition == 'IS') || ($condition == 'IS NOT')) ? $condition : 'IS';
        $this->whereSQL .= "$key $isCondition NULL $combine ";
    }

    private function flattenWhereConditions($whereConditions)
    {
        $whereConditionsReturn = [];
        foreach ($whereConditions as $whereCondition) {
            if (!empty($whereCondition[0]) && is_array($whereCondition[0])) {
                $whereConditionsReturn = \array_merge($whereConditionsReturn, $this->flattenWhereConditions($whereCondition));
            } else {
                $whereConditionsReturn[] = $whereCondition;
            }
        }
        return $whereConditionsReturn;
    }

    private function retrieveConditions($whereConditions)
    {
        $whereConditions = $this->flattenWhereConditions($whereConditions);
        $whereKey = [];
        $whereValue = [];
        $operator = [];
        $extra = [];
        $combiner = [];
        foreach ($whereConditions as $checkFields) {
            $operator[] = (isset($checkFields[1])) ? $checkFields[1] : '';
            if (empty($checkFields[1])) {
                $this->clearPrepare();
                return [[], [], [], [], []];
            }

            if (\strtoupper($checkFields[1]) == 'IN') {
                $whereKey[] = $checkFields[0];
                $whereValue[] = \array_slice((array) $checkFields, 2);
                $combiner[] = \_AND;
                $extra[] = null;
            } else {
                if (!empty($checkFields[0])) {
                    $whereKey[] = $checkFields[0];
                    $whereValue[] = (isset($checkFields[2])) ? $checkFields[2] : '';
                    $combiner[] = (isset($checkFields[3])) ? $checkFields[3] : \_AND;
                    $extra[] = (isset($checkFields[4])) ? $checkFields[4] : null;
                }
            }
        }

        return [$operator, $whereKey, $whereValue, $combiner, $extra];
    }

    private function processConditions($column, $condition, $value, $valueOrCombine, $extraCombine)
    {
        if (!\in_array($condition, \_BOOLEAN_OPERATORS))
            return $this->clearPrepare();

        if (($condition == \_BETWEEN) || ($condition == \_notBETWEEN)) {
            $this->conditionBetween($column, $condition, $value, $valueOrCombine, $extraCombine);
        } elseif ($condition == \_IN) {
            $this->conditionIn($column, $condition, $value, $valueOrCombine);
        } elseif (((\strtolower($value) == 'null') || ($condition == 'IS') || ($condition == 'IS NOT'))) {
            $this->conditionIs($column, $condition, $valueOrCombine);
        } elseif ((($condition == \_LIKE) || ($condition == \_notLIKE)) && !\preg_match('/[_%?]/', $value)) {
            return $this->clearPrepare();
        } else {
            $this->conditions($column, $condition, $value, $valueOrCombine, $extraCombine);
        }
    }

    public function grouping(...$whereConditions)
    {
        if (empty($whereConditions))
            return false;

        $whereOrHaving = ($this->isWhere) ? 'WHERE' : 'HAVING';
        if (\is_string($whereConditions[0]) && \strpos($whereConditions[0],  $whereOrHaving) !== false)
            return $whereConditions[0];

        $totalConditions = \count($whereConditions) - 1;
        if ($totalConditions > 0) {
            if (!\in_array('(', $whereConditions[0]))
                $whereConditions[0][\count($whereConditions[0])] = '(';

            if (!\in_array(')', $whereConditions[$totalConditions]))
                $whereConditions[$totalConditions][\count($whereConditions[$totalConditions])] = ')';
        }

        return $whereConditions;
    }

    public function where(...$conditions)
    {

        if (empty($conditions))
            return false;

        $whereOrHaving = ($this->isWhere) ? 'WHERE' : 'HAVING';
        $this->isWhere = true;

        $this->combineWith = '';

        if (\is_string($conditions[0]) && \strpos($conditions[0],  $whereOrHaving) !== false)
            return $conditions[0];

        list($operator, $whereKeys, $whereValues, $combiner, $extra) = $this->retrieveConditions($conditions);
        if (empty($operator))
            return false;

        $where = '1';
        if (!empty($whereKeys)) {
            $this->whereSQL = '';
            $i = 0;
            foreach ($whereKeys as $key) {
                $isCondition = \strtoupper($operator[$i]);
                $combine = $combiner[$i];
                $this->combineWith = \_AND;
                if (\in_array(\strtoupper($combine), \_COMBINERS) || isset($extra[$i]))
                    $this->combineWith = isset($extra[$i]) ? $combine : \strtoupper($combine);

                if ($this->processConditions($key, $isCondition, $whereValues[$i],  $this->combineWith, $extra[$i]) === false)
                    return false;

                $i++;
            }

            $where = \rtrim($this->whereSQL, " $this->combineWith ");
            $this->whereSQL = null;
        }

        if (($this->isPrepareOn()) && !empty($this->prepareValues()) && ($where != '1'))
            return " $whereOrHaving $where ";

        return ($where != '1') ? " $whereOrHaving $where " : ' ';
    }

    public function select(string $table = null, $columnFields = '*', ...$conditions)
    {
        $getFromTable = $this->fromTable;
        $getSelect_result = $this->select_result;
        $getIsInto = $this->isInto;

        $this->fromTable = null;
        $this->select_result = true;
        $this->isInto = false;

        $skipWhere = false;
        $whereKeys = $conditions;
        $where = '';

        if (empty($table)) {
            return $this->clearPrepare();
        }

        $columns = $this->to_string($columnFields);

        if (isset($getFromTable) && !$getIsInto)
            $sql = "CREATE TABLE $table AS SELECT $columns FROM " . $getFromTable;
        elseif (isset($getFromTable) && $getIsInto)
            $sql = "SELECT $columns INTO $table FROM " . $getFromTable;
        else
            $sql = "SELECT $columns FROM " . $table;

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
                    if (\strpos($checkFor, 'JOIN') !== false) {
                        $args_by .= $checkFor;
                        $joinSet = true;
                    } elseif (\strpos($checkFor, 'WHERE') !== false) {
                        $args_by .= $checkFor;
                        $skipWhere = true;
                    } elseif (\strpos($checkFor, 'GROUP BY') !== false) {
                        $args_by .= ' ' . $checkFor;
                        $groupBySet = true;
                    } elseif (\strpos($checkFor, 'HAVING') !== false) {
                        if ($groupBySet) {
                            $args_by .= ' ' . $checkFor;
                            $havingSet = true;
                        } else {
                            return $this->clearPrepare();
                        }
                    } elseif (\strpos($checkFor, 'ORDER BY') !== false) {
                        $args_by .= ' ' . $checkFor;
                        $orderBySet = true;
                    } elseif (\strpos($checkFor, 'LIMIT') !== false) {
                        $args_by .= ' ' . $checkFor;
                        $limitSet = true;
                    } elseif (\strpos($checkFor, 'UNION') !== false) {
                        $args_by .= ' ' . $checkFor;
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

        if (!$skipWhere)
            $where = $this->where(...$whereKeys);

        if (\is_string($where)) {
            $sql .= $where;
            if ($getSelect_result)
                return (($this->isPrepareOn()) && !empty($this->prepareValues()))
                    ? $this->get_results($sql, \OBJECT, true)
                    : $this->get_results($sql);
            return $sql;
        }

        return $this->clearPrepare();
    }

    /**
     * Get SQL statement string from `select` method instead of executing get_result
     * @return string
     */
    private function select_sql($table = '', $columnFields = '*', ...$conditions)
    {
        $this->select_result = false;
        return $this->select($table, $columnFields, ...$conditions);
    }

    public function union(string $table = null, $columnFields = '*', ...$conditions)
    {
        return 'UNION ' . $this->select_sql($table, $columnFields, ...$conditions);
    }

    public function unionAll(string $table = null, $columnFields = '*', ...$conditions)
    {
        return 'UNION ALL ' . $this->select_sql($table, $columnFields, ...$conditions);
    }

    public function create_select(string $newTable, $fromColumns = '*', $oldTable = null, ...$fromWhereConditions)
    {
        if (isset($oldTable))
            $this->fromTable = $oldTable;
        else {
            return $this->clearPrepare();
        }

        $newTableFromTable = $this->select_sql($newTable, $fromColumns, ...$fromWhereConditions);
        if (is_string($newTableFromTable))
            return (($this->isPrepareOn()) && !empty($this->prepareValues()))
                ? $this->query($newTableFromTable, true)
                : $this->query($newTableFromTable);

        return $this->clearPrepare();
    }

    public function select_into(string $newTable, $fromColumns = '*', $oldTable = null, ...$fromWhereConditions)
    {
        $this->isInto = true;
        if (isset($oldTable))
            $this->fromTable = $oldTable;
        else
            return $this->clearPrepare();

        $newTableFromTable = $this->select_sql($newTable, $fromColumns, ...$fromWhereConditions);
        if (is_string($newTableFromTable))
            return (($this->isPrepareOn()) && !empty($this->prepareValues()))
                ? $this->query($newTableFromTable, true)
                : $this->query($newTableFromTable);

        return $this->clearPrepare();
    }

    public function update(string $table = null, $keyValue, ...$whereConditions)
    {
        if (!\is_array($keyValue) || empty($table)) {
            return $this->clearPrepare();
        }

        $sql = "UPDATE $table SET ";

        foreach ($keyValue as $key => $val) {
            if (\strtolower($val) == 'null') {
                $sql .= "$key = NULL, ";
            } elseif (\in_array(\strtolower($val), array('current_timestamp()', 'date()', 'now()'))) {
                $sql .= "$key = CURRENT_TIMESTAMP(), ";
            } else {
                if ($this->isPrepareOn()) {
                    $sql .= "$key = " . \_TAG . ", ";
                    $this->addPrepare($val);
                } else
                    $sql .= "$key = '" . $this->escape($val) . "', ";
            }
        }

        $where = $this->where(...$whereConditions);
        if (\is_string($where)) {
            $sql = \rtrim($sql, ', ') . $where;
            return (($this->isPrepareOn()) && !empty($this->prepareValues()))
                ? $this->query($sql, true)
                : $this->query($sql);
        }

        return $this->clearPrepare();
    }

    public function delete(string $table = null, ...$whereConditions)
    {
        if (empty($table)) {
            return $this->clearPrepare();
        }

        $sql = "DELETE FROM $table";

        $where = $this->where(...$whereConditions);
        if (\is_string($where)) {
            $sql .= $where;
            return (($this->isPrepareOn()) && !empty($this->prepareValues()))
                ? $this->query($sql, true)
                : $this->query($sql);
        }

        return $this->clearPrepare();
    }

    /**
     * Helper does the actual insert or replace query with an array
     * @return mixed bool/results - false for error
     */
    private function _query_insert_replace($table = '', $keyValue = null, $type = '', $execute = true)
    {
        if ((!\is_array($keyValue) && ($execute)) || empty($table)) {
            return $this->clearPrepare();
        }

        if (!\in_array(strtoupper($type), array('REPLACE', 'INSERT'))) {
            return $this->clearPrepare();
        }

        $sql = "$type INTO $table";
        $value = '';
        $index = '';

        if ($execute) {
            foreach ($keyValue as $key => $val) {
                $index .= "$key, ";
                if (\strtolower($val) == 'null')
                    $value .= "NULL, ";
                elseif (\in_array(\strtolower($val), array('current_timestamp()', 'date()', 'now()')))
                    $value .= "CURRENT_TIMESTAMP(), ";
                else {
                    if ($this->isPrepareOn()) {
                        $value .= _TAG . ", ";
                        $this->addPrepare($val);
                    } else
                        $value .= "'" . $this->escape($val) . "', ";
                }
            }

            $sql .= "(" . \rtrim($index, ', ') . ") VALUES (" . \rtrim($value, ', ') . ");";

            if (($this->isPrepareOn()) && !empty($this->prepareValues()))
                $ok = $this->query($sql, true);
            else
                $ok = $this->query($sql);

            if ($ok)
                return $this->insertId;

            return $this->clearPrepare();
        } else {
            if (\is_array($keyValue)) {
                if (\array_keys($keyValue) === \range(0, \count($keyValue) - 1)) {
                    foreach ($keyValue as $key) {
                        $index .= "$key, ";
                    }
                    $sql .= " (" . \rtrim($index, ', ') . ") ";
                } else {
                    return false;
                }
            }

            return $sql;
        }
    }

    public function replace(string $table = null, $keyValue)
    {
        return $this->_query_insert_replace($table, $keyValue, 'REPLACE');
    }

    public function insert(string $table = null, $keyValue)
    {
        return $this->_query_insert_replace($table, $keyValue, 'INSERT');
    }

    public function insert_select(string $toTable = null, $toColumns = '*', $fromTable = null, $fromColumns = '*', ...$conditions)
    {
        $putToTable = $this->_query_insert_replace($toTable, $toColumns, 'INSERT', false);
        $getFromTable = $this->select_sql($fromTable, $fromColumns, ...$conditions);

        if (\is_string($putToTable) && \is_string($getFromTable))
            return (($this->isPrepareOn()) && !empty($this->prepareValues()))
                ? $this->query($putToTable . " " . $getFromTable, true)
                : $this->query($putToTable . " " . $getFromTable);

        return $this->clearPrepare();
    }

    // get_results call template
    public function get_results(string $query = null, $output = \OBJECT, bool $use_prepare = false)
    {
        return array();
    }

    //

    /**
     * query call template
     *
     * @param string $query
     * @param bool $use_prepare
     * @return bool|mixed
     */
    public function query(string $query, bool $use_prepare = false)
    {
        return false;
    }

    // escape call template if not available by vendor
    public function escape(string $str)
    {
        if (empty($str))
            return '';
        if (\is_numeric($str))
            return $str;

        $nonDisplayable = array(
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',             // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/',                   // 11
            '/\x0c/',                   // 12
            '/[\x0e-\x1f]/'             // 14-31
        );

        foreach ($nonDisplayable as $regex)
            $str = \preg_replace($regex, '', $str);

        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        $replace = array("\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z");

        return \str_replace($search, $replace, $str);
    }

    /**
     * Creates an database schema from array
     *  - column, datatype, value/options/key arguments.
     * @param array ...$columnDataOptions
     * @return string|bool - SQL schema string, or false for error
     */
    private function create_schema(array ...$columnDataOptions)
    {
        if (empty($columnDataOptions))
            return false;

        $columnData = '';
        foreach ($columnDataOptions as $datatype) {
            $column = \array_shift($datatype);
            $type = \array_shift($datatype);
            if (!empty($column) && !empty($type))
                $columnData .= column($column, $type, ...$datatype);
        }

        $schemaColumns = !empty($columnData) ? \rtrim($columnData, ', ') : null;
        if (\is_string($schemaColumns))
            return $schemaColumns;

        return false;
    }

    public function create(string $table = null, ...$schemas)
    {
        $vendor = get_vendor();
        if (empty($table) || empty($schemas) || empty($vendor))
            return false;

        $sql = 'CREATE TABLE IF NOT EXISTS ' . $table . '( ';

        $skipSchema = false;
        if (\is_string($schemas[0])) {
            $data = '';
            $stringTypes = ezSchema::STRINGS['common'];
            $stringTypes += ezSchema::STRINGS[$vendor];
            $numericTypes = ezSchema::NUMERICS['common'];
            $numericTypes += ezSchema::NUMERICS[$vendor];
            $numberTypes = ezSchema::NUMBERS['common'];
            $numberTypes += ezSchema::NUMBERS[$vendor];
            $dateTimeTypes = ezSchema::DATE_TIME['common'];
            $dateTimeTypes += ezSchema::DATE_TIME[$vendor];
            $objectTypes = ezSchema::OBJECTS[$vendor];
            $allowedTypes = ezSchema::OPTIONS;

            $stringPattern = "/" . \implode('|', $stringTypes) . "/i";
            $numericPattern = "/" . \implode('|', $numericTypes) . "/i";
            $numberPattern = "/" . \implode('|', $numberTypes) . "/i";
            $dateTimePattern = "/" . \implode('|', $dateTimeTypes) . "/i";
            $objectPattern = "/" . \implode('|', $objectTypes) . "/i";
            $patternOther = "/" . \implode('|', $allowedTypes) . "/i";
            foreach ($schemas as $types) {
                if (\preg_match($stringPattern, $types)) {
                    $data .= $types;
                    $skipSchema = true;
                } elseif (\preg_match($numericPattern, $types)) {
                    $data .= $types;
                    $skipSchema = true;
                } elseif (\preg_match($numberPattern, $types)) {
                    $data .= $types;
                    $skipSchema = true;
                } elseif (\preg_match($dateTimePattern, $types)) {
                    $data .= $types;
                    $skipSchema = true;
                } elseif (\preg_match($objectPattern, $types)) {
                    $data .= $types;
                    $skipSchema = true;
                } elseif (\preg_match($patternOther, $types)) {
                    $data .= $types;
                    $skipSchema = true;
                }
            }
            $schema = $skipSchema ? \rtrim($data, ', ') : $data;
        }

        if (!$skipSchema) {
            $schema = $this->create_schema(...$schemas);
        }

        $createTable = !empty($schema) ? $sql . $schema . ' );' : null;
        if (\is_string($createTable))
            return $this->query($createTable);

        return false;
    }

    public function alter(string $table = null, ...$alteringSchema)
    {
        if (empty($table) || empty($alteringSchema))
            return false;

        $sql = 'ALTER TABLE ' . $table . ' ';

        $skipSchema = false;
        if (\is_string($alteringSchema[0])) {
            $data = '';
            $allowedTypes = ezSchema::ALTERS;
            $pattern = "/" . \implode('|', $allowedTypes) . "/i";
            foreach ($alteringSchema as $types) {
                if (\preg_match($pattern, $types)) {
                    $data .= $types;
                    $skipSchema = true;
                }
            }
            $schema = $skipSchema ? \rtrim($data, ', ') : $data;
        }

        if (!$skipSchema)
            $schema = $this->create_schema(...$alteringSchema);

        $alterTable = !empty($schema) ? $sql . $schema . ';' : null;
        if (\is_string($alterTable))
            return $this->query($alterTable);

        return false;
    }

    public function drop(string $table = null)
    {
        if (empty($table))
            return false;

        $drop = 'DROP TABLE IF EXISTS ' . $table . ';';

        return $this->query($drop);
    }

    public function selecting($columns = '*', ...$conditions)
    {
        $table = $this->table_prefix();
        return ($table === false) ? false : $this->select($table, $columns, ...$conditions);
    }

    public function inserting(array $keyValue)
    {
        $table = $this->table_prefix();
        return ($table === false) ? false : $this->insert($table, $keyValue);
    }

    public function replacing(array $keyValue)
    {
        $table = $this->table_prefix();
        return ($table === false) ? false : $this->replace($table, $keyValue);
    }

    public function updating(array $keyValue, ...$whereConditions)
    {
        $table = $this->table_prefix();
        return ($table === false) ? false : $this->update($table, $keyValue, ...$whereConditions);
    }

    public function deleting(...$whereConditions)
    {
        $table = $this->table_prefix();
        return ($table === false) ? false : $this->delete($table, ...$whereConditions);
    }

    public function creating(...$schemas)
    {
        $table = $this->table_prefix();
        return ($table === false) ? false : $this->create($table, ...$schemas);
    }

    public function dropping()
    {
        $table = $this->table_prefix();
        return ($table === false) ? false : $this->drop($table);
    }

    public function altering(...$alteringSchema)
    {
        $table = $this->table_prefix();
        return ($table === false) ? false : $this->alter($table, ...$alteringSchema);
    }

    /**
     * Check and return the stored database `table` preset with any `prefix`.
     *
     * @return boolean|string `false` if no preset.
     */
    protected function table_prefix()
    {
        if (empty($this->table) || !\is_string($this->table))
            return $this->clearPrepare();

        $table = (!empty($this->prefix) && \is_string($this->prefix))
            ? $this->prefix . $this->table
            : $this->table;

        return $table;
    }
}
