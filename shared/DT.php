<?php
namespace ezsql;

class DT
{
    const STRINGS = [
        'shared' => ['CHAR', 'VARCHAR', 'TEXT'],
        'mysql' => ['TINYTEXT', 'MEDIUMTEXT', 'LONGTEXT', 'BINARY', 'VARBINARY'],
        'postgresql' => ['character', 'character varying'],
        'sqlserver' => ['NCHAR', 'NVARCHAR', 'NTEXT', 'BINARY', 'VARBINARY', 'IMAGE'],
        'sqlite3' => ['TINYTEXT', 'MEDIUMTEXT', 'LONGTEXT', 'NCHAR', 'NVARCHAR', 'CLOB']
    ];    

    const NUMERICS = [
        'shared' => ['INT', 'NUMERIC', 'DECIMAL'],
        'mysql' => [
            'BIT', 'INTEGER', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT',
            'DEC', 'FIXED', 'FLOAT', 'DOUBLE', 'DOUBLE PRECISION', 'REAL', 'FLOAT', 
            'BOOL', 'BOOLEAN'
        ],
        'postgresql' => [
            'bit', 'varbit', 'bit varying', 'smallint', 'int', 'integer', 
            'bigint', 'smallserial', 'serial', 'bigserial', 'double precision', 'real', 
            'money', 'bool', 'boolean'
        ],
        'sqlserver' => [
            'BIT', 'TINYINT', 'SMALLINT', 'BIGINT', 'DEC', 'FLOAT', 'REAL', 
            'SMALLMONEY', 'MONEY'
        ],
        'sqlite3' => ['TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'INTEGER', 'INT2', 
            'INT4', 'INT8', 'REAL', 'DOUBLE', 'DOUBLE PRECISION', 'FLOAT', 'BOOLEAN']
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
        'sqlite3' => ['BLOB']
    ];
    
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
        elseif ($dbPdo === \getInstance() && !empty($dbPdo))
            $type = 'pdo';

        return $type;
    }
	
	public function __call($type, $args) 
	{
        $vendor = DI::vendor();
        $stringTypes = DT::STRINGS['shared'];
        $stringTypes += DT::STRINGS[$vendor];
        $numericTypes = DT::NUMERICS['shared'];
        $numericTypes += DT::NUMERICS[$vendor];
        $dateTimeTypes = DT::DATE_TIME['shared'];
        $dateTimeTypes += DT::DATE_TIME[$vendor];
        $objectTypes = DT::OBJECTS[$vendor];

        $stringPattern = "/".\implode('|', $stringTypes)."/i";
        $numericPattern = "/".\implode('|', $numericTypes)."/i";
        $dateTimePattern = "/".\implode('|', $dateTimeTypes)."/i";
        $objectPattern = "/".\implode('|', $objectTypes)."/i";
		
		$data = null;

		if (\preg_match($stringPattern, $type)) {
			// check for string data type
			$size = !empty($args[0]) ? '('.$args[0].')' : '';
			$value = !empty($args[1]) ? ' '.$args[1] : '';
			$data = $type.$size.$value;
		} elseif (\preg_match($numericPattern, $type)) {
			// check for numeric data type
			$size = !empty($type[1]) && is_array($type) ? '('.$type[1][0].','.$type[1][1].') ' : ' ';
			$value = !empty($args[1]) ? ' '.$args[1] : '';
			$data .= $type[0].$size.$value;
			$data .= $type[0].' ';
        } elseif (\preg_match($dateTimePattern, $type)) {
			// check for date time data type
			$data .= $type[0].' ';
        } elseif (\preg_match($objectPattern, $type)) {
			// check for large object data type
			$data = $type.(!empty($args[0]) ? ' '.$args[0] : '');
			$data .= $type[0].' ';
        }

		return $data;
	}
} 
