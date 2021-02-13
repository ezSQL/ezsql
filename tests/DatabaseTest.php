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

use function ezsql\functions\tagInstance;

class DatabaseTest extends EZTestCase
{
    public function testInitialize()
    {
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped(
                'The MySQLi extension is not available.'
            );
        }

        $mysqli = Database::initialize(\MYSQLI, [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME], MYSQLI);
        $this->assertFalse($mysqli instanceof ConfigInterface);
        $this->assertTrue($mysqli->settings() instanceof ConfigInterface);
        $this->assertTrue($mysqli instanceof ez_mysqli);
        $this->assertTrue($mysqli->getShow_Errors());
        $benchmark = Database::benchmark();
        $this->assertNotNull($benchmark['start']);
        $this->assertSame($mysqli, tagInstance(MYSQLI));
    }

    public function testInitialize_Pgsql()
    {
        if (!extension_loaded('pgsql')) {
            $this->markTestSkipped(
                'The PostgreSQL Lib is not available.'
            );
        }

        $pgsql = Database::initialize(\PGSQL, [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT]);
        $this->assertFalse($pgsql instanceof ConfigInterface);
        $this->assertTrue($pgsql->settings() instanceof ConfigInterface);
        $this->assertTrue($pgsql instanceof ez_pgsql);
        $this->assertTrue($pgsql->getShow_Errors());
        $benchmark = Database::benchmark();
        $this->assertNotNull($benchmark['start']);
    }

    public function testInitialize_Sqlite3()
    {
        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped(
                'The sqlite3 Lib is not available.'
            );
        }

        $sqlite3 = Database::initialize(\SQLITE3, [self::TEST_SQLITE_DB_DIR, self::TEST_SQLITE_DB]);
        $this->assertFalse($sqlite3 instanceof ConfigInterface);
        $this->assertTrue($sqlite3->settings() instanceof ConfigInterface);
        $this->assertTrue($sqlite3 instanceof ez_sqlite3);
        $this->assertTrue($sqlite3->getShow_Errors());
        $benchmark = Database::benchmark();
        $this->assertNotNull($benchmark['start']);
    }

    public function testInitialize_Sqlsrv()
    {
        if (!extension_loaded('sqlsrv')) {
            $this->markTestSkipped(
                'The sqlsrv Lib is not available.'
            );
        }

        $sqlsrv = Database::initialize(\SQLSERVER, [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->assertFalse($sqlsrv instanceof ConfigInterface);
        $this->assertTrue($sqlsrv->settings() instanceof ConfigInterface);
        $this->assertTrue($sqlsrv instanceof ez_sqlsrv);
        $this->assertTrue($sqlsrv->getShow_Errors());
        $benchmark = Database::benchmark();
        $this->assertNotNull($benchmark['start']);
    }

    public function testInitialize_Pdo()
    {
        if (!\class_exists('PDO')) {
            $this->markTestSkipped(
                'The PDO Lib is not available.'
            );
        }

        $pdo = Database::initialize(
            Pdo,
            ['mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=3306', self::TEST_DB_USER, self::TEST_DB_PASSWORD]
        );
        $this->assertFalse($pdo instanceof ConfigInterface);
        $this->assertTrue($pdo->settings() instanceof ConfigInterface);
        $this->assertTrue($pdo instanceof ez_pdo);
        $this->assertTrue($pdo->getShow_Errors());
        $benchmark = Database::benchmark();
        $this->assertNotNull($benchmark['start']);
    }

    public function testInitialize_Error()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/[Missing configuration details]/');
        $mysqli = Database::initialize('', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
    }
}
