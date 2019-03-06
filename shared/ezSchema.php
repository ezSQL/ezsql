<?php
namespace ezsql;

class ezSchema
{
    const STRINGS = [
        'shared' => ['CHAR', 'VARCHAR', 'TEXT'],
        'mysql' => ['TINYTEXT', 'MEDIUMTEXT', 'LONGTEXT', 'BINARY', 'VARBINARY'],
        'postgresql' => ['character', 'character varying'],
        'sqlserver' => ['NCHAR', 'NVARCHAR', 'NTEXT', 'BINARY', 'VARBINARY', 'IMAGE'],
        'sqlite3' => ['TINYTEXT', 'MEDIUMTEXT', 'LONGTEXT', 'NCHAR', 'NVARCHAR', 'CLOB']
    ];    

    const NUMBERS = [
		'shared' => ['INT'],
        'mysql' => ['BIT', 'INTEGER', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'FLOAT',
			'BOOL', 'BOOLEAN'],
        'postgresql' => ['bit', 'varbit', 'bit varying', 'smallint', 'int', 'integer', 
            'bigint', 'smallserial', 'serial', 'bigserial', 'double precision', 'real', 
            'money', 'bool', 'boolean'],
        'sqlserver' => ['BIT', 'TINYINT', 'SMALLINT', 'BIGINT', 'SMALLMONEY', 'MONEY',
			'FLOAT', 'REAL'],
        'sqlite3' => ['TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'INTEGER', 'INT2', 
            'INT4', 'INT8', 'REAL', 'DOUBLE', 'DOUBLE PRECISION', 'FLOAT', 'BOOLEAN']
    ];

    const NUMERICS = [
        'shared' => ['NUMERIC', 'DECIMAL'],
        'mysql' => ['DEC', 'FIXED', 'FLOAT', 'DOUBLE', 'DOUBLE PRECISION', 'REAL'],
        'postgresql' => [],
        'sqlserver' => ['DEC'],
        'sqlite3' => []
    ];

    const DATE_TIME = [
        'shared' => ['DATE', 'TIMESTAMP', 'TIME'],
        'mysql' => ['DATETIME', 'YEAR'],            
        'postgresql' => [
            'timestamp without time zone', 'timestamp with time zone', 
            'time without time zone', 'time with time zone'
        ],        
        'sqlserver' => ['DATETIME', 'DATETIME2', 'SMALLDATETIME', 'DATETIMEOFFSET'],
        'sqlite3' => ['DATETIME']
    ];

    const OBJECTS  = [
        'mysql' => ['TINYBLOB', 'BLOB', 'MEDIUMBLOB', 'LONGTEXT'],
        'sqlite3' => ['BLOB'],
        'postgresql' => [],
        'sqlserver' => []
    ];

    const OPTIONS  = ['CONSTRAINT', 'PRIMARY KEY'];

    private $arguments = null;
    
    public static function vendor() 
    {
        $type = null;
        $dbSqlite = $GLOBALS['db_sqlite'];
        $dbPgsql = $GLOBALS['db_pgsql'];
        $dbMysqli = $GLOBALS['db_mysqli'];
        $dbMssql = $GLOBALS['db_mssql'];
        $dbPdo = $GLOBALS['db_pdo'];
        if ($dbSqlite === \getInstance() && !empty($dbSqlite))
            $type = 'sqlite3';
        elseif ($dbPgsql === \getInstance() && !empty($dbPgsql)) 
            $type = 'postgresql';
        elseif ($dbMysqli === \getInstance() && !empty($dbMysqli))
            $type = 'mysql';
        elseif ($dbMssql === \getInstance() && !empty($dbMssql))
            $type = 'sqlserver';
        elseif ($dbPdo === \getInstance() && !empty($dbPdo)) {
            if (strpos($dbPdo->getAttribute(\PDO::ATTR_CLIENT_VERSION), 'mysql') !== false) 
                $type = 'mysql';
            elseif (strpos($dbPdo->getAttribute(\PDO::ATTR_CLIENT_VERSION), 'pgsql') !== false) 
                $type = 'postgresql';
            elseif (strpos($dbPdo->getAttribute(\PDO::ATTR_CLIENT_VERSION), 'sqlite') !== false) 
                $type = 'sqlite3';
            elseif (strpos($dbPdo->getAttribute(\PDO::ATTR_CLIENT_VERSION), 'sqlsrv') !== false) 
                $type = 'sqlserver';
        }

        return $type;
    }
	
	public function __construct( ...$args)
    {
        $this->arguments = $args;
    }

	public function __call($type, $args) 
	{
        $vendor = self::vendor();
        if (empty($vendor))
            return false;

        $args = $this->arguments;
        $stringTypes = self::STRINGS['shared'];
        $stringTypes += self::STRINGS[$vendor];
        $numericTypes = self::NUMERICS['shared'];
        $numericTypes += self::NUMERICS[$vendor];
        $numberTypes = self::NUMBERS['shared'];
        $numberTypes += self::NUMBERS[$vendor];
        $dateTimeTypes = self::DATE_TIME['shared'];
        $dateTimeTypes += self::DATE_TIME[$vendor];
        $objectTypes = self::OBJECTS[$vendor];

        $stringPattern = "/".\implode('|', $stringTypes)."/i";
        $numericPattern = "/".\implode('|', $numericTypes)."/i";
        $numberPattern = "/".\implode('|', $numberTypes)."/i";
        $dateTimePattern = "/".\implode('|', $dateTimeTypes)."/i";
        $objectPattern = "/".\implode('|', $objectTypes)."/i";
		
		$data = null;
		if (\preg_match($stringPattern, $type)) {
			// check for string data type
			$store = !empty($args[0]) ? '('.$args[0].')' : '';
			$value = !empty($args[1]) ? $args[1] : '';
			$options = !empty($args[2]) ? $args[2] : '';
			$extra = !empty($args[3]) ? ' '.$args[3] : '';
			$data = $type.$store.' '.$value.' '.$options.$extra;
		} elseif (\preg_match($numericPattern, $type)) {
			// check for numeric data type
			$size = '('.(!empty($args[0]) ? $args[0] : '6').',';
			$size .= (!empty($args[1]) ? $args[1] : '2').') ';
			$value = !empty($args[2]) ? $args[2] : '';
			$options = !empty($args[3]) ? $args[3] : '';
			$extra = !empty($args[4]) ? ' '.$args[4] : '';
			$data = $type.$size.' '.$value.' '.$options.$extra;
		} elseif (\preg_match($numberPattern, $type)) {
            // check for numeric data type
            $numberOrString = $args[0];
			$store = \is_int($numberOrString) ? '('.$numberOrString.')' : '';
			$store = empty($store) && !empty($numberOrString) ? $numberOrString : $store;
			$value = !empty($args[1]) ? $args[1] : '';
			$options = !empty($args[2]) ? $args[2] : '';
			$extra = !empty($args[3]) ? ' '.$args[3] : '';
			$data = $type.$store.' '.$value.' '.$options.$extra;
        } elseif (\preg_match($dateTimePattern, $type)) {
			// check for date time data type
			$fraction = !empty($args[0]) ? '('.$args[0].')' : '';
			$value = !empty($args[1]) ? $args[1] : '';
			$options = !empty($args[2]) ? $args[2] : '';
			$data = $type.$fraction.' '.$value.' '.$options;
        } elseif (\preg_match($objectPattern, $type)) {
			// check for large object data type
			$value = !empty($args[0]) ? ' '.$args[0] : '';
			$data = $type.$value;
        } else {
            throw new \Exception("$type does not exist");
        }

		return $data;
    }

    /**
    * Convert array to string, and attach '`, `' for separation, if none is provided.
    *
    * @return string
    */  
    private static function to_string($arrays, $separation = ',' )  
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

    /**
     * Creates an database column, 
     * - column, datatype, value/options with the given arguments.
     * 
     * @param string $column, - or CONSTRAINT,
     * @param string $type, - data type for the column
     * @param mixed $size, 
     * @param mixed $value, - column should be NULL or NOT NULL. If omitted, assumes NULL
     * @param mixed $default - Optional. It is the value to assign to the column
     * 
     * @return string|bool - SQL schema string, or false for error
     */
    public static function column(string $column = null, string $type = null, ...$args)
    {
        if (empty($column) || empty($type))
            return false;

        $columnData = '';
        if ($column == \CONSTRAINT) {
            if (empty($args[0]))
                 return false;
            $columnData = $column.' '.$type.' '.\PRIMARY.' ('.self::to_string($args).'), ';
        } else {
            $data = self::datatype($type, $args);
            if (!empty($data))
                $columnData = $column.' '.$data.', ';
        }

        $schemaColumns = !empty($columnData) ? \rtrim($columnData, ', ') : null;
        if (\is_string($schemaColumns))
            return $schemaColumns;

        return false;
    }

    /**
    * Creates an datatype with given arguments.
    * 
    * @param string $type,
    * @param mixed $size, 
    * @param mixed $value, 
    * @param mixed $default
    * 
    * @return string
    */
   public static function datatype(string $type, ...$args)	
   {
       $data = new self( ...$args);
       return $data->$type();
   }

    /**
     * Creates an database schema from array
     *  - column, datatype, value/options with the given arguments.
     * 
     * @param string $column,
     * @param string $type,
     * @param mixed $size, 
     * @param mixed $value, 
     * @param mixed $default
     * 
     * @return string|bool - SQL schema string, or false for error
     */
   private function create_schema(array ...$columnDataOptions) 
   {
       if (empty($columnDataOptions))
           return false;

       $columnData = '';
       foreach($columnDataOptions as $datatype) {
           $column = \array_shift($datatype);
           $type = \array_shift($datatype);
           if ($column == \CONSTRAINT) {
               if (empty($datatype[0]))
                    return false;
               $columnData .= $column.' '.$type.' '.\PRIMARY.' ('.self::to_string($datatype).'), ';
           } else {
               $data = self::datatype($type, $datatype);
                if (!empty($data))
                    $columnData .= $column.' '.$data.', ';
           }
       }

       $schemaColumns = !empty($columnData) ? \rtrim($columnData, ', ') : null;
       if (\is_string($schemaColumns))
           return $schemaColumns;

       return false;
   }

   /**
    * Creates an database table and columns, by either:
    *  - array( column, datatype, ...value/options arguments ) // calls create_schema() 
    *  - column( column, datatype, ...value/options arguments ) // returns string
    * 
    * @param string $table, - The name of the db table that you wish to create
    * @param mixed $schemas, - An array of:
    *
    * @param string $column, - or CONSTRAINT,
    * @param string $type, - data type for the column
    * @param mixed $size, 
    * @param mixed $value, - column should be NULL or NOT NULL. If omitted, assumes NULL
    * @param mixed $default - Optional. It is the value to assign to the column
    * 
    * @return mixed results of query() call
    */
   public function create(string $table = null, ...$schemas) 
   {
        $vendor = self::vendor();
        if (empty($table) || empty($schemas) || empty($vendor))
           return false;

        $sql = 'CREATE TABLE '.$table.' ( ';

        $skipSchema = false;
        if (\is_string($schemas[0])) {
            $data = '';
            $allowedTypes = self::STRINGS['shared'];
            $allowedTypes += self::STRINGS[$vendor];
            $allowedTypes += self::NUMERICS['shared'];
            $allowedTypes += self::NUMERICS[$vendor];
            $allowedTypes += self::NUMBERS['shared'];
            $allowedTypes += self::NUMBERS[$vendor];
            $allowedTypes += self::DATE_TIME['shared'];
            $allowedTypes += self::DATE_TIME[$vendor];
            $allowedTypes += self::OBJECTS[$vendor];
            $allowedTypes += self::OPTIONS;
            $pattern = "/".\implode('|', $allowedTypes)."/i";
            foreach($schemas as $types) {
                if (\preg_match($pattern, $types)) {
                    $data .= $types;
                    $skipSchema = true;
                }
            }
            $schema = $skipSchema ? \rtrim($data, ', ') : $data;
        }

        if (! $skipSchema) {
            $schema = $this->create_schema( ...$schemas);
        }

        $createTable = !empty($schema) ? $sql.$schema.' );' : null;
        if (\is_string($createTable))
            return $this->query($createTable);

        return false;
   }
} 
