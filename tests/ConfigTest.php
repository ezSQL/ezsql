<?php

namespace ezsql\Tests;

use ezsql\Config;
use ezsql\ConfigAbstract;
use ezsql\ConfigInterface;
use ezsql\Tests\EZTestCase;

class ConfigTest extends EZTestCase 
{
    /**
    * @covers ezsql\Config::SetupMysqli
    */
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

    /**
    * @covers ezsql\Config::SetupPdo
    */
    public function testSetupPdo()
    {
        if ( ! \class_exists ('PDO') ) {
            $this->markTestSkipped(
              'The PDO Lib is not available.'
            );
        }

        $dsn = 'mysql:host='.self::TEST_DB_HOST.';dbname='. self::TEST_DB_NAME.';port='.self::TEST_DB_PORT;
        $settings = Config::initialize('pdo', [$dsn, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
        $this->assertTrue($settings instanceof ConfigAbstract);
        $this->assertEquals($dsn, $settings->getDsn());
        $this->assertEquals(self::TEST_DB_USER, $settings->getUser());
        $this->assertEquals(self::TEST_DB_PASSWORD, $settings->getPassword());
    }

    /**
    * @covers ezsql\Config::SetupPgsql
    */
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

    /**
    * @covers ezsql\Config::SetupSqlsrv
    */
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

    /**
    * @covers ezsql\Config::SetupSqlite3
    */
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

    /**
    * @covers ezsql\Config::__construct
    */
    public function test_construct()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/[Missing configuration details to connect to database]/');
        $settings = new Config('', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
    }

    /**
    * @covers ezsql\Config::__construct
    */
    public function test_constructArgs()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/[Missing configuration details to connect to database]/');
        $settings = new Config('mysqli');
    }
} 
