<?php

if (!\defined('CONSTANTS')) {
    // ezQuery prepare placeholder/positional tag
    \define('_TAG', '__ez__');
    // Use to set get_result output as json 
    \define('_JSON', 'json');
 
    /**
    * Operator boolean expressions.
    */
    \define('EQ', '=');
    \define('NEQ', '<>');
    \define('NE', '!=');
    \define('LT', '<');
    \define('LTE', '<=');
    \define('GT', '>');
    \define('GTE', '>=');
    \define('_BOOLEAN', ['<', '>', '=', '!=', '>=', '<=', '<>']);
    
    \define('_IN', 'IN');
    \define('_notIN', 'NOT IN');
    \define('_LIKE', 'LIKE');
    \define('_notLIKE', 'NOT LIKE');
    \define('_BETWEEN', 'BETWEEN');
    \define('_notBETWEEN', 'NOT BETWEEN');
        
    \define('_isNULL', 'IS NULL');
    \define('_notNULL', 'IS NOT NULL');
    \define('_BOOLEAN_OPERATORS', ['<', '>', '=', '!=', '>=', '<=', '<>', 
        'IN', 'LIKE', 'NOT LIKE', 'BETWEEN', 'NOT BETWEEN', 'IS', 'IS NOT']);
    
    /**
    * Combine operators.
    */    
    \define('_AND', 'AND');
    \define('_OR', 'OR');
    \define('_NOT', 'NOT');
    \define('_andNOT', 'AND NOT');
    \define('_COMBINERS', ['AND', 'OR', 'NOT', 'AND NOT']);

    /*
    * for joining shortcut methods.
    */    
    \define('_INNER', 'INNER');
    \define('_LEFT', 'LEFT');
    \define('_RIGHT', 'RIGHT');
    \define('_FULL', 'FULL'); 
    \define('_JOINERS', ['INNER', 'LEFT', 'RIGHT', 'FULL']); 
 
    /**
    * Associative array of supported SQL Drivers, and library
    * @define(array)
    */
    \define('VENDOR', [
        'mysql' => 'ez_mysqli',
        'mysqli' => 'ez_mysqli',
        'pdo' => 'ez_pdo',
        'postgresql' => 'ez_pgsql',
        'pgsql' => 'ez_pgsql',
        'sqlite' => 'ez_sqlite3',
        'sqlite3' => 'ez_sqlite3',
        'sqlserver' => 'ez_sqlsrv',
        'mssql' => 'ez_sqlsrv',
        'sqlsrv' => 'ez_sqlsrv'
    ]);

    \define('mysql', 'mysqli');
    \define('mysqli', 'mysqli');
    \define('pdo', 'pdo');
    \define('pgsql', 'pgsql');
    \define('postgresql', 'postgresql');
    \define('sqlite', 'sqlite');
    \define('sqlite3', 'sqlite3');
    \define('sqlsrv', 'sqlsrv');
    \define('sqlserver', 'sqlserver');
    \define('mssql', 'mssql');

    \define('ALLOWED_KEYS', [
        'host',
        'hostname',
        'user',
        'username',
        'password',
        'database',
        'db',
        'name',
        'dsn',
        'char',
        'charset',
        'path',
        'port',
        'file',
        'filebase',
        'nosql',
        'nomysql',
        'options'
    ]);
        
    \define('KEY_MAP', [
        'host' => 'host',
        'hostname' => 'host',
        'user' => 'user',
        'username' => 'user',
        'pass' => 'password',
        'password' => 'password',
        'database' => 'name',
        'db' => 'name',
        'name' => 'name',
        'dsn' => 'dsn',
        'char' => 'charset',
        'charset' => 'charset',
        'path' => 'path',
        'port' => 'port',
        'file' => 'isFile',
        'filebase' => 'isFile',
        'nosql' => 'toMysql',
        'nomysql' => 'toMysql',
        'options' => 'options'
    ]);

    // String SQL data types
    \define('CHAR', 'CHAR');
    \define('VARC', 'VARCHAR');
    \define('VARCHAR', 'VARCHAR');
    \define('TEXT', 'TEXT');
    \define('TINY', 'TINYTEXT');
    \define('TINYTEXT', 'TINYTEXT');
    \define('MEDIUM', 'MEDIUMTEXT');
    \define('MEDIUMTEXT', 'MEDIUMTEXT');
    \define('LONG', 'LONGTEXT');
    \define('LONGTEXT', 'LONGTEXT');
    \define('BINARY', 'BINARY');
    \define('VARBINARY', 'VARBINARY');
    \define('NCHAR', 'NCHAR');
    \define('NVAR', 'NVARCHAR');
    \define('NVARCHAR', 'NVARCHAR');
    \define('NTEXT', 'NTEXT');
    \define('IMAGE', 'IMAGE');
    \define('CLOB', 'CLOB');
        
    // Numeric SQL data types
    \define('INTR', 'INT');
    \define('INT0', 'INT');
    \define('INT2', 'INT2');
    \define('INT4', 'INT4');
    \define('INT8', 'INT8');
    \define('NUMERIC', 'NUMERIC');
    \define('DECIMAL', 'DECIMAL');
    \define('BIT', 'BIT');
    \define('VARBIT', 'VARBIT');
    \define('INTEGERS', 'INTEGER');
    \define('TINYINT', 'TINYINT');
    \define('SMALLINT', 'SMALLINT');
    \define('MEDIUMINT', 'MEDIUMINT');
    \define('LARGE', 'BIGINT');
    \define('BIGINT', 'BIGINT');
    \define('DEC', 'DEC');
    \define('FIXED', 'FIXED');
    \define('FLOATS', 'FLOAT');
    \define('DOUBLES', 'DOUBLE');
    \define('REALS', 'REAL');
    \define('BOOLS', 'BOOL');
    \define('BOOLEANS', 'BOOLEAN');
    \define('SMALLMONEY', 'SMALLMONEY');
    \define('MONEY', 'MONEY');
        
    // Date/Time SQL data types	
    \define('DATES', 'DATE');
    \define('TIMESTAMP', 'TIMESTAMP');
    \define('TIMES', 'TIME');
    \define('DATETIME', 'DATETIME');
    \define('YEAR', 'YEAR');
    \define('DATETIME2', 'DATETIME2');
    \define('SMALLDATETIME', 'SMALLDATETIME');
    \define('DATETIMEOFFSET', 'DATETIMEOFFSET');
        
    // Large Object SQL data types
    \define('TINYBLOB', 'TINYBLOB');
    \define('BLOB', 'BLOB');
    \define('MEDIUMBLOB', 'MEDIUMBLOB');
        
    \define('NULLS', 'NULL');
    \define('notNULL', 'NOT NULL');

    \define('CONSTRAINT', 'CONSTRAINT');
    \define('PRIMARY', 'PRIMARY KEY');
    \define('FOREIGN', 'FOREIGN KEY');
    \define('UNIQUE', 'UNIQUE');
    \define('INDEX', 'INDEX');
    \define('REFERENCES', 'REFERENCES');

    \define('AUTO', '__autoNumbers__');
    \define('AUTO_INCREMENT', 'AUTO_INCREMENT');
    \define('AUTOINCREMENT', 'AUTOINCREMENT');
    \define('IDENTITY', 'IDENTITY');
    \define('SERIAL', 'SERIAL');
    \define('SMALLSERIAL', 'SMALLSERIAL');
    \define('BIGSERIAL', 'BIGSERIAL');

    \define('ADD', 'ADD');
    \define('DROP', 'DROP COLUMN');
    \define('CHANGE', 'CHANGE COLUMN');
    \define('ALTER', 'ALTER COLUMN');
    \define('MODIFY', 'MODIFY');
    \define('RENAME', 'RENAME TO');
    \define('CHANGER', '__modifyingColumns__');

    if (!\defined('_DS'))
        \define('_DS', \DIRECTORY_SEPARATOR);

    \define('CONSTANTS', true);
}
