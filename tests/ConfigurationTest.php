<?php

namespace ezsql\Tests;

use ezsql\Configuration;
use ezsql\ConfigAbstract;
use ezsql\Tests\EZTestCase;

class ConfigurationTest extends EZTestCase 
{		
    /**
    * @covers ezsql\Configuration::SetupMysqli
    */
    public function testSetupMysqli()
    {
        $settings = new Configuration('mysqli', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->assertTrue($settings instanceof ConfigAbstract);
        $this->assertEquals(self::TEST_DB_USER, $settings->getUser());
        $this->assertEquals(self::TEST_DB_PASSWORD, $settings->getPassword());
        $this->assertEquals(self::TEST_DB_NAME, $settings->getName());
    }

    /**
    * @covers ezsql\Configuration::SetupPdo
    */
    public function testSetupPdo()
    {
        $dsn = 'mysql:host='.self::TEST_DB_HOST.';dbname='. self::TEST_DB_NAME.';port='.self::TEST_DB_PORT;
        $settings = new Configuration('pdo', [$dsn, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
        $this->assertTrue($settings instanceof ConfigAbstract);
        $this->assertEquals($dsn, $settings->getDsn());
        $this->assertEquals(self::TEST_DB_USER, $settings->getUser());
        $this->assertEquals(self::TEST_DB_PASSWORD, $settings->getPassword());
    }

    /**
    * @covers ezsql\Configuration::SetupPgsql
    */
    public function testSetupPgsql()
    {
        $settings = new Configuration('pgsql', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT]);
        $this->assertTrue($settings instanceof ConfigAbstract);
        $this->assertEquals(self::TEST_DB_USER, $settings->getUser());
        $this->assertEquals(self::TEST_DB_PASSWORD, $settings->getPassword());
        $this->assertEquals(self::TEST_DB_NAME, $settings->getName());
        $this->assertEquals(self::TEST_DB_HOST, $settings->getHost());
        $this->assertEquals(self::TEST_DB_PORT, $settings->getPort());
    }

    /**
    * @covers ezsql\Configuration::SetupSqlsrv
    */
    public function testSetupSqlsrv()
    {
        $settings = new Configuration('sqlsrv', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->assertTrue($settings instanceof ConfigAbstract);
        $this->assertEquals(self::TEST_DB_USER, $settings->getUser());
        $this->assertEquals(self::TEST_DB_PASSWORD, $settings->getPassword());
        $this->assertEquals(self::TEST_DB_NAME, $settings->getName());
    }

    /**
    * @covers ezsql\Configuration::SetupSqlite3
    */
    public function testSetupSqlite3()
    {
        $settings = new Configuration('sqlite3', [self::TEST_SQLITE_DB_DIR, self::TEST_SQLITE_DB]);
        $this->assertTrue($settings instanceof ConfigAbstract);
        $this->assertEquals(self::TEST_SQLITE_DB_DIR, $settings->getPath());
        $this->assertEquals(self::TEST_SQLITE_DB, $settings->getName());
    }
    
    /**
    * @covers ezsql\Configuration::__construct
    */
    public function test__construct()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/[Missing configuration details to connect to database]/');
        $settings = new Configuration('', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
    }
   
    /**
    * @covers ezsql\Configuration::__construct
    */
    public function test__constructArgs()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/[Missing configuration details to connect to database]/');
        $settings = new Configuration('mysqli');
    }
} 
