<?php

namespace ezsql\Tests;

use ezsql\ezSchema;
use ezsql\Tests\EZTestCase;
use function ezsql\functions\{
    pdoInstance,
    pgsqlInstance,
    mysqlInstance,
    mssqlInstance,
    sqliteInstance,
    clearInstance,
    get_vendor,
    column,
    primary,
    index
};

class ezSchemaTest extends EZTestCase
{
    public function testVendor()
    {
        clearInstance();
        $this->assertEquals(null, get_vendor());
        $this->assertEquals(false, ezSchema::datatype(BLOB, NULLS));
        $this->assertFalse(column('id', INTR, 32, AUTO, PRIMARY));
    }

    public function testVendor_mysqli()
    {
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped(
                'The MySQLi extension is not available.'
            );
        }

        mysqlInstance([self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->assertEquals(MYSQLI, get_vendor());
        $this->assertEquals('BLOB NULL', ezSchema::datatype(BLOB, NULLS));
        $this->assertEquals('VARCHAR(256) NOT NULL', ezSchema::datatype(VARCHAR, 256, notNULL));
        $this->assertEquals('id INT(32) AUTO_INCREMENT PRIMARY KEY, ', column('id', INTR, 32, AUTO, PRIMARY));
    }

    public function testDatatype_mysqli()
    {
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped(
                'The MySQLi extension is not available.'
            );
        }

        $db = mysqlInstance([self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $result = $db->create(
            'profile',
            'id ' . ezSchema::datatype(INTR, 11, PRIMARY) . ', ',
            'name ' . ezSchema::datatype(VARCHAR, 256, notNULL) . ', '
        );

        $this->assertEquals(0, $result);
        $db->drop('profile');
    }

    public function testColumn()
    {
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped(
                'The MySQLi extension is not available.'
            );
        }

        $db = mysqlInstance([self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $result = $db->create(
            'profile',
            column('id', INTR, 32, AUTO),
            column('name', CHAR, 32, notNULL),
            primary('id_pk', 'id'),
            index('name_dx', 'name')
        );

        $this->assertEquals(0, $result);
        $db->drop('profile');
    }

    public function test__call()
    {
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped(
                'The MySQLi extension is not available.'
            );
        }

        $db = mysqlInstance([self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $result = $db->create(
            'profile',
            column('id', INTR, 32, AUTO, PRIMARY),
            column('name', VARCHAR, 256, notNULL),
            column('price', NUMERIC, 6, 2),
            column('date', TIMESTAMP, notNULL),
            column('pics', BLOB, NULLS)
        );

        $this->assertEquals(0, $result);
        $db->drop('profile');
    }

    public function test__call_Error()
    {
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped(
                'The MySQLi extension is not available.'
            );
        }

        clearInstance();
        $this->assertFalse(column('id', INTR, 32, AUTO, PRIMARY));
        $db = mysqlInstance([self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/[does not exist]/');
        $this->assertNull(column('id', 'DOS', 32));
    }

    public function testVendor_Pgsql()
    {
        if (!extension_loaded('pgsql')) {
            $this->markTestSkipped(
                'The PostgreSQL Lib is not available.'
            );
        }

        pgsqlInstance([self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT]);
        $this->assertEquals(PGSQL, get_vendor());
        $this->assertEquals('TIMESTAMP NOT NULL', ezSchema::datatype(TIMESTAMP, notNULL));
        $this->assertEquals('price NUMERIC(6,2) NULL, ', column('price', NUMERIC, 6, 2, NULLS));
        $this->assertEquals('id SERIAL PRIMARY KEY, ', column('id', AUTO, PRIMARY));
    }

    public function testVendor_Sqlite3()
    {
        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped(
                'The sqlite3 Lib is not available.'
            );
        }

        sqliteInstance([self::TEST_SQLITE_DB_DIR, self::TEST_SQLITE_DB]);
        $this->assertEquals(SQLITE3, get_vendor());
    }

    public function testVendor_Sqlsrv()
    {
        if (!extension_loaded('sqlsrv')) {
            $this->markTestSkipped(
                'The sqlsrv Lib is not available.'
            );
        }

        mssqlInstance([self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->assertEquals(MSSQL, get_vendor());
    }

    public function testVendor_Pdo()
    {
        if (!\class_exists('PDO')) {
            $this->markTestSkipped(
                'The PDO Lib is not available.'
            );
        }

        $pdo_mysql = pdoInstance(['mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=3306', self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
        $pdo_mysql->connect();
        $this->assertEquals(MYSQLI, get_vendor());
    }

    public function test__construct()
    {
        $this->assertNotNull(new ezSchema('test'));
    }
}
