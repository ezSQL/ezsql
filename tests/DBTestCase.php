<?php

namespace ezsql\Tests;

use PHPUnit\Framework\TestCase;

class DBTestCase extends TestCase 
{
    /**
     * constant string user name
     */
    const TEST_DB_USER = 'ez_test';

    /**
     * constant string password
     */
    const TEST_DB_PASSWORD = 'ezTest';

    /**
     * constant database name
     */
    const TEST_DB_NAME = 'ez_test';

    /**
     * constant database host
     */
    const TEST_DB_HOST = 'localhost';

    /**
     * constant database connection charset
     */
    const TEST_DB_CHARSET = 'utf8';

    /**
     * constant string database port
     */
    const TEST_DB_PORT = '5432';
    
    /**
     * constant string path and file name of the SQLite test database
     */
    const TEST_SQLITE_DB = 'ez_test.sqlite';

    private $errors;
 
    function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) 
    {
        $this->errors[] = compact("errno", "errstr", "errfile", "errline", "errcontext");
    }

    function assertError($errstr, $errno) 
    {
        foreach ($this->errors as $error) {
            if ($error["errstr"] === $errstr && $error["errno"] === $errno) {
                return;
            }
        }

        $this->fail("Error with level ".$errno
            ." and message '".$errstr
            ."' not found in ", 
            var_export($this->errors, TRUE)
        );
    }   
 }
