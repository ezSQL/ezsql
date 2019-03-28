<?php

require 'vendor/autoload.php';

use ezsql\ezSchema;
use PHPUnit\Framework\TestCase;

class ezSchemaTest extends TestCase 
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
     * constant database port 
     */
    const TEST_DB_PORT = '5432';

    /**
     * constant string path and file name of the SQLite test database
     */
    const TEST_SQLITE_DB = 'ez_test.sqlite3';
    const TEST_SQLITE_DB_DIR = './tests/sqlite/';

    /**
    * @covers ezsql\ezSchema::vendor
    */
    public function testVendor()
    {
        setQuery();
        $this->assertEquals(null, ezSchema::vendor());
        $this->assertEquals(false, ezSchema::datatype(BLOB, NULLS));
        $this->assertFalse(column('id', INTR, 32, AUTO, PRIMARY));
    }

    /**
    * @covers ezsql\ezSchema::vendor
    */
    public function testVendor_mysqli()
    {
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped(
              'The MySQLi extension is not available.'
            );
        }

        $object = new ezSQL_mysqli;
        $object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);

        $this->assertEquals(MYSQLI, ezSchema::vendor());
        $this->assertEquals('BLOB NULL', ezSchema::datatype(BLOB, NULLS));
        $this->assertEquals('VARCHAR(256) NOT NULL', ezSchema::datatype(VARCHAR, 256, notNULL));
        $this->assertEquals('id INT(32) AUTO_INCREMENT PRIMARY KEY, ', column('id', INTR, 32, AUTO, PRIMARY));
    }

    /**
    * @covers ezsql\ezSchema::vendor
    */
    public function testVendor_Pgsql()
    {
        if (!extension_loaded('pgsql')) {
            $this->markTestSkipped(
              'The PostgreSQL Lib is not available.'
            );
        }

        $object = new ezSQL_postgresql; 
        $object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
        $this->assertEquals(PGSQL, ezSchema::vendor());
        $this->assertEquals('TIMESTAMP NOT NULL', ezSchema::datatype(TIMESTAMP, notNULL));
        $this->assertEquals('price NUMERIC(6,2) NULL, ', column('price', NUMERIC, 6, 2, NULLS));
        $this->assertEquals('id SERIAL PRIMARY KEY, ', column('id', AUTO, PRIMARY));
    }

    /**
    * @covers ezsql\ezSchema::vendor
    */
    public function testVendor_Sqlite3()
    {
        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped(
              'The sqlite3 Lib is not available.'
            );
        }
        
        $object = new ezSQL_sqlite3(self::TEST_SQLITE_DB_DIR, self::TEST_SQLITE_DB); 
        $this->assertEquals(SQLITE3, ezSchema::vendor());
    }

    /**
    * @covers ezsql\ezSchema::vendor
    */
    public function testVendor_Sqlsrv()
    {
        if (!extension_loaded('sqlsrv')) {
            $this->markTestSkipped(
              'The sqlsrv Lib is not available.'
            );
        }

        $object = new ezSQL_sqlsrv;
        $object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->assertEquals(MSSQL, ezSchema::vendor());
    }
} 
