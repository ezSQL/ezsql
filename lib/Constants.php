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
    
    \define('_IN', 'IN');
    \define('_notIN', 'NOT IN');
    \define('_LIKE', 'LIKE');
    \define('_notLIKE', 'NOT LIKE');
    \define('_BETWEEN', 'BETWEEN');
    \define('_notBETWEEN', 'NOT BETWEEN');
        
    \define('_isNULL', 'IS NULL');
    \define('_notNULL', 'IS NOT NULL');
    
    /**
    * Combine operators.
    */    
    \define('_AND', 'AND');
    \define('_OR', 'OR');
    \define('_NOT', 'NOT');
    \define('_andNOT', 'AND NOT');
 
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
        'mssql' => 'ez_sqlsrv',
        'sqlsrv' => 'ez_sqlsrv'
    ]);

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
        'file' => 'isfile',
        'filebase' => 'isfile',
        'nosql' => 'to_mysql',
        'nomysql' => 'to_mysql',
        'options' => 'options'
    ]);

    \define('CONSTANTS', true);
}
