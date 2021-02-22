<?php

if (!\defined('CONSTANTS')) {

    /**
     * ezsqlModel Constants
     */
    \defined('EZSQL_VERSION') or \define('EZSQL_VERSION', '5.1.1');
    \defined('OBJECT') or \define('OBJECT', 'OBJECT');
    \defined('ARRAY_A') or \define('ARRAY_A', 'ARRAY_A');
    \defined('ARRAY_N') or \define('ARRAY_N', 'ARRAY_N');
    // Use to set get_result output as json
    \define('JSON', 'json');

    // Error messages
    \define('MISSING_CONFIGURATION', '<b>Fatal Error:</b> Missing configuration details to connect to database');
    \define('CONFIGURATION_REQUIRES', '<b>Fatal Error:</b> This configuration requires ezsqlModel (ezsqlModel.php) to be included/loaded before it can be used');
    \define('FAILED_CONNECTION', 'Failed to make connection to database');

    // ezQuery prepare placeholder/positional tag
    \define('_TAG', '__ez__');

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
    \define('_BOOLEAN_OPERATORS', [
        '<', '>', '=', '!=', '>=', '<=', '<>',
        'IN', 'LIKE', 'NOT LIKE', 'BETWEEN', 'NOT BETWEEN', 'IS', 'IS NOT'
    ]);

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
        'mysql' => 'ezsql\Database\ez_mysqli',
        'mysqli' => 'ezsql\Database\ez_mysqli',
        'pdo' => 'ezsql\Database\ez_pdo',
        'postgresql' => 'ezsql\Database\ez_pgsql',
        'pgsql' => 'ezsql\Database\ez_pgsql',
        'sqlite' => 'ezsql\Database\ez_sqlite3',
        'sqlite3' => 'ezsql\Database\ez_sqlite3',
        'sqlserver' => 'ezsql\Database\ez_sqlsrv',
        'mssql' => 'ezsql\Database\ez_sqlsrv',
        'sqlsrv' => 'ezsql\Database\ez_sqlsrv'
    ]);

    \define('MYSQL', 'mysqli');
    \define('MYSQLI', 'mysqli');
    \define('Pdo', 'pdo');
    \define('PGSQL', 'pgsql');
    \define('POSTGRESQL', 'pgsql');
    \define('SQLITE', 'sqlite3');
    \define('SQLITE3', 'sqlite3');
    \define('SQLSRV', 'sqlsrv');
    \define('SQLSERVER', 'sqlsrv');
    \define('MSSQL', 'sqlsrv');

    // String SQL data types
    \define('CHAR', 'CHAR');
    \define('VARCHAR', 'VARCHAR');
    \define('CHARACTER', 'CHARACTER');
    \define('TEXT', 'TEXT');
    \define('TINY', 'TINYTEXT');
    \define('TINYTEXT', 'TINYTEXT');
    \define('MEDIUM', 'MEDIUMTEXT');
    \define('MEDIUMTEXT', 'MEDIUMTEXT');
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
    \define('SEQUENCE', '__autoNumbers__');
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
    \define('MODIFY', 'MODIFY COLUMN');
    \define('RENAME', 'RENAME TO');
    \define('CHANGER', '__modifyingColumns__');

    if (!\defined('_DS'))
        \define('_DS', \DIRECTORY_SEPARATOR);

    \define('CONSTANTS', true);
}
