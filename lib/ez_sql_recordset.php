<?php
/**
 * ezSQL Database specific class for working with query results
 * Desc..: recordset component (part of ezSQL databse abstraction library)
 *
 * @author  Stefanie Janine Stoelting <mail@stefanie-stoelting.de>
 * @name    ezSQL_recordset
 * @package ezSQL
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 *
 */
class ezSQL_recordset implements Iterator
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
     * The current position in the recordset
     * @var int
     */
    private $_position = 0;

    /**
     * Contains the possible return types
     * @var array
     */
    private $_checkTypes = array(
        'array'
        , 'object'
        , 'row'
    );

    /**
     * The recordset
     * @var array
     */
    private $_recordset = array();


    /**
     * Initializes the record object
     *
     * @param array $ezSQL_queryresult The result of an ezSQL query
     * @throws Exception When $ezSQL_queryresult is not an array
     */
    public function __construct($ezSQL_queryresult) {
        if (!is_array($ezSQL_queryresult)) {
            throw new Exception('$ezSQL_queryresult is not valid.');
        }

        $this->_recordset = $ezSQL_queryresult;
        $this->position = 0;
        
        global $_ezRecordset;
        $_ezRecordset = $this;
    } // __construct

    /**
     * Sets the position to zero
     */
    public function rewind() {
        $this->_position = 0;
    } // rewind

    /**
     * Returns the current row of the recordset as stdClass, which is the
     * default mode, or as array as fieldname - fieldvalue.
     *
     * @param string $mode Return the current row as array, or object
     *                     Default is RESULT_AS_OBJECT
     * @return stdClass/array
     */
    public function current($mode=self::RESULT_AS_OBJECT) {
        $return_val = null;

        if (!in_array($mode, $this->_checkTypes)) {
            throw new Exception(sprintf('$mode is not in %s1 or %s2', self::RESULT_AS_OBJECT, self::RESULT_AS_ARRAY));
        }

        if ($this->valid()) {
            switch ($mode) {
                case self::RESULT_AS_OBJECT:
                    // The result is a standard ezSQL row of stdClass
                    $return_val = $this->_recordset[$this->_position];

                    break;

                case self::RESULT_AS_ARRAY:
                    $return_val = get_object_vars($this->_recordset[$this->_position]);

                    break;

                case self::RESULT_AS_ROW:
                    $return_val = array_values(get_object_vars($this->_recordset[$this->_position]));
                    
                    break;

                default:

                    break;
            }
        } else {
            $result = false;
        }

        return $return_val;
    } // current

    /**
     * Returns the current position in the recordset
     *
     * @return int
     */
    public function key() {
        return $this->_position;
    } // key

    /**
     * Sets the position of the recordset up by one
     */
    public function next() {
        ++$this->_position;
    } // next

    /**
     * Sets position of the recordset down by one, if the position is below the
     * start, the position is set to the start position
     */
    public function previous() {
        --$this->_position;

        if ($this->_position < 0) {
            $this->_position = 0;
        }
    } // previous

    /**
     * Whether the current position contains a row, or not
     *
     * @return boolean
     */
    public function valid() {
        return isset($this->_recordset[$this->_position]);
    } // valid

    /**
     * Behaves like mysql_fetch_assoc. This method it to implement ezSQL easier
     * in an existing system, that made us of mysql_fetch_assoc.
     * It returns the current record as an associative array and moves the
     * internal data pointer ahead.
     *
     * @return array
     */
    public function ezSQL_fetch_assoc() {
        if ($this->valid()) {
            $return_val = $this->current(self::RESULT_AS_ARRAY);
            $this->next();
        } else {
            $return_val = false;
        }

        return $return_val;
    } // ezSQL_fetch_assoc

    /**
     * Behaves like mysql_fetch_row This method it to implement ezSQL easier
     * in an existing system, that made us of mysql_fetch_row.
     * It returns the current record as a numeric array and moves the internal
     * data pointer ahead.
     *
     * @return array
     */
    public function ezSQL_fetch_row() {
        if ($this->valid()) {
            $return_val = $this->current(self::RESULT_AS_ROW);
            $this->next();
        } else {
            $return_val = false;
        }

        return $return_val;
    } // ezSQL_fetch_row

    /**
     * Behaves like mysql_fetch_object This method it to implement ezSQL easier
     * in an existing system, that made us of mysql_fetch_object.
     * It returns n object with properties that correspond to the fetched row
     * and moves the internal data pointer ahead.
     *
     * @return array
     */
    public function ezSQL_fetch_object() {
        if ($this->valid()) {
            $return_val = $this->current(self::RESULT_AS_OBJECT);
            $this->next();
        } else {
            $return_val = false;
        }

        return $return_val;
    } // ezSQL_fetch_object
    //public function

} // dbapi_recordset