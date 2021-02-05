<?php

namespace ezsql\Tests;

use ezsql\Config;
use ezsql\ConfigAbstract;
use ezsql\ConfigInterface;
use ezsql\Tests\EZTestCase;

class ConfigTest extends EZTestCase
{
    public function testSetupMysqli()
    {
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped(
                'The MySQLi extension is not available.'
            );
        }

        $settings = new Config('mysqli', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->assertTrue($settings instanceof ConfigInterface);
        $this->assertEquals(self::TEST_DB_USER, $settings->getUser());
        $this->assertEquals(self::TEST_DB_PASSWORD, $settings->getPassword());
        $this->assertEquals(self::TEST_DB_NAME, $settings->getName());
    }

    public function testInitializeMysqli()
    {
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped(
                'The MySQLi extension is not available.'
            );
        }

        $settings = Config::initialize('mysqli', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->assertTrue($settings instanceof ConfigInterface);
    }

    public function testErrorMysqli()
    {
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped(
                'The MySQLi extension is not available.'
            );
        }

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/[Missing configuration details to connect to database]/');
        $settings = Config::initialize('mysqli', [self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
    }

    public function testSetupPdo()
    {
        if (!\class_exists('PDO')) {
            $this->markTestSkipped(
                'The PDO Lib is not available.'
            );
        }

        $dsn = 'mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=3306';
        $settings = new Config('pdo', [$dsn, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
        $this->assertTrue($settings instanceof ConfigAbstract);
    }

    public function testInitializePdo()
    {
        if (!\class_exists('PDO')) {
            $this->markTestSkipped(
                'The PDO Lib is not available.'
            );
        }

        $dsn = 'mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=3306';
        $settings = Config::initialize('pdo', [$dsn, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
        $this->assertTrue($settings instanceof ConfigAbstract);
        $this->assertEquals($dsn, $settings->getDsn());
        $this->assertEquals(self::TEST_DB_USER, $settings->getUser());
        $this->assertEquals(self::TEST_DB_PASSWORD, $settings->getPassword());
    }

    public function testErrorPdo()
    {
        if (!\class_exists('PDO')) {
            $this->markTestSkipped(
                'The PDO Lib is not available.'
            );
        }

        $dsn = 'mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=3306';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/[Missing configuration details to connect to database]/');
        $settings = Config::initialize('pdo', [$dsn]);
    }

    public function test__callPdo()
    {
        if (!\class_exists('PDO')) {
            $this->markTestSkipped(
                'The PDO Lib is not available.'
            );
        }

        $dsn = 'mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=3306';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/[does not exist]/');
        $settings = new Config('pdo', [$dsn, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
        $settings->getNotAnProperty();
    }

    public function testSetupPgsql()
    {
        if (!extension_loaded('pgsql')) {
            $this->markTestSkipped(
                'The PostgreSQL Lib is not available.'
            );
        }

        $settings = new Config('pgsql', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT]);
        $this->assertTrue($settings instanceof ConfigInterface);
        $this->assertEquals(self::TEST_DB_USER, $settings->getUser());
        $this->assertEquals(self::TEST_DB_PASSWORD, $settings->getPassword());
        $this->assertEquals(self::TEST_DB_NAME, $settings->getName());
        $this->assertEquals(self::TEST_DB_HOST, $settings->getHost());
        $this->assertEquals(self::TEST_DB_PORT, $settings->getPort());
    }

    public function testInitializePgsql()
    {
        if (!extension_loaded('pgsql')) {
            $this->markTestSkipped(
                'The PostgreSQL Lib is not available.'
            );
        }

        $settings = Config::initialize('pgsql', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT]);
        $this->assertTrue($settings instanceof ConfigInterface);
    }

    public function testErrorPgsql()
    {
        if (!extension_loaded('pgsql')) {
            $this->markTestSkipped(
                'The PostgreSQL Lib is not available.'
            );
        }

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/[Missing configuration details to connect to database]/');
        $settings = Config::initialize('pgsql', [self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
    }

    public function testSetupSqlsrv()
    {
        if (!extension_loaded('sqlsrv')) {
            $this->markTestSkipped(
                'The sqlsrv Lib is not available.'
            );
        }

        $settings = new Config('sqlsrv', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->assertTrue($settings instanceof ConfigAbstract);
        $this->assertEquals(self::TEST_DB_USER, $settings->getUser());
        $this->assertEquals(self::TEST_DB_PASSWORD, $settings->getPassword());
        $this->assertEquals(self::TEST_DB_NAME, $settings->getName());
    }

    public function testInitializeSqlsrv()
    {
        if (!extension_loaded('sqlsrv')) {
            $this->markTestSkipped(
                'The sqlsrv Lib is not available.'
            );
        }

        $settings = Config::initialize('sqlsrv', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->assertTrue($settings instanceof ConfigAbstract);
    }

    public function testErrorSqlsrv()
    {
        if (!extension_loaded('sqlsrv')) {
            $this->markTestSkipped(
                'The sqlsrv Lib is not available.'
            );
        }

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/[Missing configuration details to connect to database]/');
        $settings = new Config('sqlsrv', [self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
    }

    public function testSetupSqlite3()
    {
        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped(
                'The sqlite3 Lib is not available.'
            );
        }

        $settings = new Config('sqlite3', [self::TEST_SQLITE_DB_DIR, self::TEST_SQLITE_DB]);
        $this->assertTrue($settings instanceof ConfigInterface);
        $this->assertEquals(self::TEST_SQLITE_DB_DIR, $settings->getPath());
        $this->assertEquals(self::TEST_SQLITE_DB, $settings->getName());
    }

    public function testInitializeSqlite3()
    {
        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped(
                'The sqlite3 Lib is not available.'
            );
        }

        $settings = Config::initialize('sqlite3', [self::TEST_SQLITE_DB_DIR, self::TEST_SQLITE_DB]);
        $this->assertTrue($settings instanceof ConfigInterface);
    }

    public function testErrorSqlite3()
    {
        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped(
                'The sqlite3 Lib is not available.'
            );
        }

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/[Missing configuration details to connect to database]/');
        $settings = new Config('sqlite3', [self::TEST_SQLITE_DB_DIR]);
    }

    public function test_construct()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/[Missing configuration details to connect to database]/');
        $settings = new Config('', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
    }

    public function test_constructArgs()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/[Missing configuration details to connect to database]/');
        $settings = new Config('mysqli');
    }
}
