<?php
namespace ezsql;

class ezSchema
{
    const STRINGS = [
        'common' => ['CHAR', 'VARCHAR', 'TEXT'],
        'mysqli' => ['TINYTEXT', 'MEDIUMTEXT', 'LONGTEXT', 'BINARY', 'VARBINARY'],
        'pgsql' => ['character', 'character varying'],
        'sqlsrv' => ['NCHAR', 'NVARCHAR', 'NTEXT', 'BINARY', 'VARBINARY', 'IMAGE'],
        'sqlite3' => ['TINYTEXT', 'MEDIUMTEXT', 'LONGTEXT', 'NCHAR', 'NVARCHAR', 'CLOB']
    ];    

    const NUMBERS = [
		'common' => ['INT'],
        'mysqli' => ['BIT', 'INTEGER', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'FLOAT',
			'BOOL', 'BOOLEAN'],
        'pgsql' => ['bit', 'varbit', 'bit varying', 'smallint', 'int', 'integer', 
            'bigint', 'smallserial', 'serial', 'bigserial', 'double precision', 'real', 
            'money', 'bool', 'boolean'],
        'sqlsrv' => ['BIT', 'TINYINT', 'SMALLINT', 'BIGINT', 'SMALLMONEY', 'MONEY',
			'FLOAT', 'REAL'],
        'sqlite3' => ['TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'INTEGER', 'INT2', 
            'INT4', 'INT8', 'REAL', 'DOUBLE', 'DOUBLE PRECISION', 'FLOAT', 'BOOLEAN']
    ];

    const NUMERICS = [
        'common' => ['NUMERIC', 'DECIMAL'],
        'mysqli' => ['IDENTITY','DEC', 'FIXED', 'FLOAT', 'DOUBLE', 'DOUBLE PRECISION', 'REAL'],
        'sqlsrv' => ['DEC'],
        'pgsql' => [],
        'sqlite3' => []
    ];

    const DATE_TIME = [
        'common' => ['DATE', 'TIMESTAMP', 'TIME'],
        'mysqli' => ['DATETIME', 'YEAR'],            
        'pgsql' => [
            'timestamp without time zone', 'timestamp with time zone', 
            'time without time zone', 'time with time zone'
        ],        
        'sqlsrv' => ['DATETIME', 'DATETIME2', 'SMALLDATETIME', 'DATETIMEOFFSET'],
        'sqlite3' => ['DATETIME']
    ];

    const OBJECTS  = [
        'mysqli' => ['TINYBLOB', 'BLOB', 'MEDIUMBLOB', 'LONGTEXT'],
        'sqlite3' => ['BLOB'],
        'pgsql' => [],
        'sqlsrv' => []
    ];

    const OPTIONS  = ['CONSTRAINT', 'PRIMARY KEY', 'FOREIGN KEY', 'UNIQUE', 'INDEX', 'REFERENCES'];
    const ALTERS  = ['ADD', 'DROP COLUMN', 'CHANGE COLUMN', 'RENAME TO', 'MODIFY', 'ALTER COLUMN'];
    const CHANGES  = [
        'mysqli' => 'MODIFY',
        'pgsql' => 'ALTER COLUMN',
        'sqlsrv' => 'ALTER COLUMN',
        'sqlite3' => ''
     ];

    const autoNUMBERS  = [
        'mysqli' => 'AUTO_INCREMENT',
        'pgsql' => 'SERIAL',
        'sqlsrv' => 'IDENTITY(1,1)',
        'sqlite3' => 'AUTOINCREMENT'
     ];

    private $arguments = null;
	
	public function __construct( ...$args)
    {
        $this->arguments = $args;
    }

    /**
     * Makes an datatype callable from the above supported CONSTANTS. 
     * This is used to create the database schema.
     *
     * @param string $type
     * @param array $args
     * 
     * @return string
     * @throws Exception
     */
	public function __call($type, $args) 
	{
        $vendor = self::vendor();
        if (empty($vendor))
            return false;

        $args = $this->arguments;
        $stringTypes = self::STRINGS['common'];
        $stringTypes += self::STRINGS[$vendor];
        $numericTypes = self::NUMERICS['common'];
        $numericTypes += self::NUMERICS[$vendor];
        $numberTypes = self::NUMBERS['common'];
        $numberTypes += self::NUMBERS[$vendor];
        $dateTimeTypes = self::DATE_TIME['common'];
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
            $numberOrString = $args[0];
			$store = \is_int($numberOrString) ? '('.$numberOrString.')' : '';
			$store = empty($store) && !empty($numberOrString) ? ' '.$numberOrString : $store;
			$value = !empty($args[1]) ? ' '.$args[1] : '';
			$options = !empty($args[2]) ? ' '.$args[2] : '';
			$extra = !empty($args[3]) ? ' '.$args[3] : '';
			$data = $type.$store.$value.$options.$extra;
		} elseif (\preg_match($numericPattern, $type)) {
			// check for numeric data type
			$size = '('.(!empty($args[0]) ? $args[0] : 10 ).',';
			$size .= (!empty($args[1]) ? $args[1] : 2 ).')';
			$value = !empty($args[2]) ? ' '.$args[2] : '';
			$options = !empty($args[3]) ? $args[3] : '';
			$extra = !empty($args[4]) ? ' '.$args[4] : '';
			$data = $type.$size.$value.$options.$extra;
		} elseif (\preg_match($numberPattern, $type)) {
            // check for numeric data type
            $numberOrString = $args[0];
            $store = \is_int($numberOrString) ? '('.$numberOrString.')' : '';
			$store = empty($store) && !empty($numberOrString) ? ' '.$numberOrString : $store;
			$value = !empty($args[1]) ? ' '.$args[1] : '';
			$options = !empty($args[2]) ? ' '.$args[2] : '';
			$extra = !empty($args[3]) ? ' '.$args[3] : '';
			$data = $type.$store.$value.$options.$extra;
        } elseif (\preg_match($dateTimePattern, $type)) {
			// check for date time data type
            $numberOrString = $args[0];
			$store = \is_int($numberOrString) ? '('.$numberOrString.')' : '';
			$fraction = empty($store) && !empty($numberOrString) ? ' '.$numberOrString : $store;
			$value = !empty($args[1]) ? ' '.$args[1] : '';
			$options = !empty($args[2]) ? ' '.$args[2] : '';
			$data = $type.$fraction.$value.$options;
        } elseif (\preg_match($objectPattern, $type)) {
			// check for large object data type
			$value = !empty($args[0]) ? ' '.$args[0] : '';
			$data = $type.$value;
        } else {
            throw new \Exception("$type does not exist");
        }

		return $data;
    }

    public static function vendor() 
    {
        $type = null;
        $dbSqlite = $GLOBALS['db_sqlite'];
        $dbPgsql = $GLOBALS['db_pgsql'];
        $dbMysqli = $GLOBALS['db_mysqli'];
        $dbMssql = $GLOBALS['db_sqlsrv'];
        $dbPdo = $GLOBALS['db_pdo'];
        $instance = \getInstance();
        if ($dbSqlite === $instance && !empty($dbSqlite))
            $type = 'sqlite3';
        elseif ($dbPgsql === $instance && !empty($dbPgsql)) 
            $type = 'pgsql';
        elseif ($dbMysqli === $instance && !empty($dbMysqli))
            $type = 'mysqli';
        elseif ($dbMssql === $instance && !empty($dbMssql))
            $type = 'sqlsrv';
        elseif ($dbPdo === $instance && !empty($dbPdo)) {
            $dbh = $dbPdo->connection();
            if (strpos($dbh->getAttribute(\PDO::ATTR_CLIENT_VERSION), 'mysql') !== false) 
                $type = 'mysqli';
            elseif (strpos($dbh->getAttribute(\PDO::ATTR_CLIENT_VERSION), 'pgsql') !== false) 
                $type = 'pgsql';
            elseif (strpos($dbh->getAttribute(\PDO::ATTR_CLIENT_VERSION), 'sqlite') !== false) 
                $type = 'sqlite3';
            elseif (strpos($dbh->getAttribute(\PDO::ATTR_CLIENT_VERSION), 'sqlsrv') !== false) 
                $type = 'sqlsrv';
        }

        return $type;
    }

    /**
     * Creates an database column, 
     * - column, datatype, value/options with the given arguments.
     * 
     * // datatype are global CONSTANTS and can be written out like:
     *      - VARCHAR, 32, notNULL, PRIMARY, AUTO, ....
     * // AUTO constant will replaced with the proper auto sequence for the SQL driver
     * 
     * @param string $column|CONSTRAINT, - column name/CONSTRAINT usage for PRIMARY|FOREIGN KEY
     * @param string $type|$constraintName, - data type for column/primary|foreign constraint name
     * @param mixed $size|...$primaryForeignKeys, 
     * @param mixed $value, - column should be NULL or NOT NULL. If omitted, assumes NULL
     * @param mixed $default - Optional. It is the value to assign to the column
     * 
     * @return string|bool - SQL schema string, or false for error
     */
    public static function column(string $column = null, string $type = null, ...$args)
    {
        if (empty($column) || empty($type))
            return false;

        $vendor = self::vendor();
        $columnData = '';
        if (($column == \CONSTRAINT) || ($column == \INDEX)) {
            if (empty($args[0]) || empty($args[1])) {
                return false;
            }

            $keyType = ($column != \INDEX) ? \array_shift($args).' ' : ' ';
            $keys = $keyType.'('.\to_string($args).'), ';
            $columnData .= $column.' '.$type.' '.$keys;
        } elseif (($column == \ADD) || ($column == \DROP) || ($column == \CHANGER)) {
            if ($column != \DROP) {
                // check for modify placeholder and replace with vendors
                $column = \str_replace(\CHANGER, self::CHANGES[$vendor], $column);
                $column = $column.' '.$type;
                $type2 = \array_shift($args);
                $data = self::datatype($type2, ...$args);
            } elseif ($vendor != 'sqlite3')
                $data = $type;

            if (!empty($data))
                $columnData = $column.' '.$data.', ';
        } else {
            $data = self::datatype($type, ...$args);
            if (!empty($data)) {
		        // check for sequence placeholder and replace with vendors
		        $data = \str_replace(\AUTO, self::autoNUMBERS[$vendor], $data);
                $columnData = $column.' '.$data.', ';
            }
        }

        $schemaColumns = !empty($columnData) ? $columnData : null;
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
} 
