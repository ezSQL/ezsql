<?php

/**
 * Originally:
 *  ezSQL Database mysql specific class for working with query results record set component
 */

namespace ezsql;

class ezResultset implements \Iterator
{
    /**
     * Returns the result as array
     */
    const RESULT_AS_ARRAY = 'array';

    /**
     * Returns the result as object of stdClass
     */
    const RESULT_AS_OBJECT = 'object';

    /**
     * Returns the result as numeric array
     */
    const RESULT_AS_ROW = 'row';

    /**
     * Returns the result as json encoded
     */
    const RESULT_AS_JSON = 'json';

    /**
     * The current position in the resultset
     * @var int
     */
    private $_position = 0;

    /**
     * Contains the possible return types
     * @var array
     */
    private $_checkTypes = array('array', 'object', 'row', 'json');

    /**
     * The resultset
     * @var array
     */
    private $_resultset = array();

    /**
     * Initializes the record object
     * @param array|object $query_result The result of an ezSQL query
     * @throws Exception When $query_result is not an array
     */
    public function __construct($query_result)
    {
        if (!\is_array($query_result)) {
            throw new \Exception("$query_result is not valid.");
        }
        $this->_resultset = $query_result;
        $this->_position = 0;
    } // __construct

    /**
     * Sets the position to zero
     */
    public function rewind()
    {
        $this->_position = 0;
    } // rewind

    /**
     * Returns the current row of the resultset as stdClass, which is the
     * default mode, or as array as {field name} => {field value}.
     * @param string $mode Return the current row as array, or object
     *                      Default is RESULT_AS_OBJECT
     * @return \stdClass|array
     */
    public function current($mode = self::RESULT_AS_OBJECT)
    {
        $return_val = null;
        if (!\in_array($mode, $this->_checkTypes)) {
            throw new \Exception(\sprintf('$mode is not in %s1 or %s2', self::RESULT_AS_OBJECT, self::RESULT_AS_ARRAY));
        }

        if ($this->valid()) {
            switch ($mode) {
                case self::RESULT_AS_OBJECT:
                    // The result is a standard row of stdClass
                    $return_val = $this->_resultset[$this->_position];
                    break;
                case self::RESULT_AS_ARRAY:
                    $return_val = \get_object_vars($this->_resultset[$this->_position]);
                    break;
                case self::RESULT_AS_ROW:
                    $return_val = \array_values(\get_object_vars($this->_resultset[$this->_position]));
                    break;
                case self::RESULT_AS_JSON:
                    $return_val = \json_encode($this->_resultset[$this->_position]);
                    break;
                default:
                    throw new \Error("Invalid result fetch type");
            }
        } else {
            $result = false;
        }
        return $return_val;
    } // current

    /**
     * Returns the current position in the resultset
     * @return int
     */
    public function key()
    {
        return $this->_position;
    } // key

    /**
     * Sets the position of the resultset up by one
     */
    public function next()
    {
        ++$this->_position;
    } // next

    /**
     * Sets position of the resultset down by one, if the position is below the
     * start, the position is set to the start position
     */
    public function previous()
    {
        --$this->_position;

        if ($this->_position < 0) {
            $this->_position = 0;
        }
    } // previous

    /**
     * Whether the current position contains a row, or not
     * @return boolean
     */
    public function valid()
    {
        return isset($this->_resultset[$this->_position]);
    } // valid

    /**
     * Returns the current record as an associative array and moves the internal data pointer ahead.
     * Behaves like mysql_fetch_assoc
     * @return array
     */
    public function fetch_assoc()
    {
        if ($this->valid()) {
            $return_val = $this->current(self::RESULT_AS_ARRAY);
            $this->next();
        } else {
            $return_val = false;
        }
        return $return_val;
    } // fetch_assoc

    /**
     * Returns the current record as a numeric array and moves the internal data pointer ahead.
     * Behaves like mysql_fetch_row
     * @return array
     */
    public function fetch_row()
    {
        if ($this->valid()) {
            $return_val = $this->current(self::RESULT_AS_ROW);
            $this->next();
        } else {
            $return_val = false;
        }
        return $return_val;
    } // fetch_row

    /**
     * Returns n object with properties that correspond to the fetched row and moves
     * the internal data pointer ahead. Behaves like mysql_fetch_object.
     * @return object
     */
    public function fetch_object()
    {
        if ($this->valid()) {
            $return_val = $this->current(self::RESULT_AS_OBJECT);
            $this->next();
        } else {
            $return_val = false;
        }
        return $return_val;
    } // fetch_object

    /**
     * Returns the current record as an json object and moves the internal data pointer ahead.
     * @return string
     */
    public function fetch_json()
    {
        if ($this->valid()) {
            $return_val = $this->current(self::RESULT_AS_JSON);
            $this->next();
        } else {
            $return_val = false;
        }
        return $return_val;
    } // fetch_assoc
    //public function
} // ezResultset
