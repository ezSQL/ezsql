<?php

namespace ezsql\Tests;

use ezsql\Database;
use ezsql\ConfigInterface;
use ezsql\Database\ez_mysqli;
use ezsql\Database\ez_pdo;
use ezsql\Database\ez_pgsql;
use ezsql\Database\ez_sqlite3;
use ezsql\Database\ez_sqlsrv;
use ezsql\Tests\EZTestCase;

class DatabaseTest extends EZTestCase 
{		
    /**
    * @covers ezsql\Database::Initialize
    */
    public function testInitialize()
    {
        $mysqli = Database::initialize(MYSQLI, [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->assertFalse($mysqli instanceof ConfigInterface);
        $this->assertTrue($mysqli->settings() instanceof ConfigInterface);
        $this->assertTrue($mysqli instanceof ez_mysqli);
        $this->assertTrue($mysqli->getShow_Errors());
        $benchmark = Database::benchmark();
        $this->assertNotNull($benchmark['start']);
    }

    /**
    * @covers ezsql\Database::Initialize
    */
    public function testInitialize_Error()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/[Missing configuration details]/');
        $mysqli = Database::initialize('', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
    }
} 
