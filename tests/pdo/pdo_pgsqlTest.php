<?php

namespace ezsql\Tests\pdo;

use ezsql\Database;
use ezsql\Tests\EZTestCase;

class pdo_pgsqlTest extends EZTestCase
{
    /**
     * constant string database port
     */
    const TEST_DB_PORT = '5432';

    /**
     * constant string path and file name of the SQLite test database
     */
    const TEST_SQLITE_DB = 'ez_test.sqlite';

    /**
     * @var resource
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        if (!extension_loaded('pdo_pgsql')) {
            $this->markTestSkipped(
                'The pdo_pgsql Lib is not available.'
            );
        }

        $this->object = Database::initialize('pdo', ['pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
        $this->object->prepareOn();
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->object = null;
    } // tearDown

    /**
     * @covers ezsql\Database\ez_pdo::connect
     */
    public function testPosgreSQLConnect()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    } // testPosgreSQLConnect

    /**
     * @covers ezsql\Database\ez_pdo::quick_connect
     */
    public function testPosgreSQLQuick_connect()
    {
        $this->assertTrue($this->object->quick_connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    } // testPosgreSQLQuick_connect

    /**
     * @covers ezsql\Database\ez_pdo::escape
     */
    public function testPosgreSQLEscape()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testPosgreSQLEscape

    /**
     * @covers ezsql\Database\ez_pdo::sysDate
     */
    public function testPosgreSQLSysDate()
    {
        $this->assertEquals("datetime('now')", $this->object->sysDate());
    } // testPosgreSQLSysDate

    /**
     * @covers ezsql\Database\ez_pdo::catch_error
     */
    public function testPosgreSQLCatch_error()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->object->query('DROP TABLE unit_test2');
        $this->assertTrue($this->object->catch_error());
    } // testPosgreSQLCatch_error

    /**
     * @covers ezsql\Database\ez_pdo::query
     * @covers ezsql\Database\ez_pdo::processQuery
     * @covers ezsql\Database\ez_pdo::processResult
     */
    public function testPosgreSQLQuery()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertEquals(0, $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'));

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } // testPosgreSQLQuery

    /**
     * @covers ezsql\ezQuery::insert
     */
    public function testInsert()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), PRIMARY KEY (ID))');

        $result = $this->object->insert('unit_test', array('test_key' => 'test 1'));
        $this->assertEquals(1, $result);

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    /**
     * @covers ezsql\ezQuery::update
     */
    public function testUpdate()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));

        $this->object->insert('unit_test', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
        $result = $this->object->insert('unit_test', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));
        $this->assertEquals($result, 3);

        $unit_test['test_key'] = 'the key string';
        $where = array('test_key', '=', 'test 1');
        $this->assertEquals(1, $this->object->update('unit_test', $unit_test, $where));

        $this->assertEquals(
            1,
            $this->object->update(
                'unit_test',
                $unit_test,
                eq('test_key', 'test 3'),
                eq('test_value', 'testing string 3')
            )
        );

        $where = eq('test_value', 'testing string 4');
        $this->assertEquals(0, $this->object->update('unit_test', $unit_test, $where));

        $this->assertEquals(1, $this->object->update('unit_test', $unit_test, ['test_key', '=', 'test 2']));

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    /**
     * @covers ezsql\ezQuery::delete
     */
    public function testDelete()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
        $this->object->insert('unit_test', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
        $this->object->insert('unit_test', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));

        $where = array('test_key', '=', 'test 1');
        $this->assertEquals($this->object->delete('unit_test', $where), 1);

        $this->assertEquals($this->object->delete(
            'unit_test',
            array('test_key', '=', 'test 3'),
            array('test_value', '=', 'testing string 3')
        ), 1);
        $where = array('test_value', '=', 'testing 2');
        $this->assertEquals(0, $this->object->delete('unit_test', $where));
        $where = eq('test_key', 'test 2');
        $this->assertEquals(1, $this->object->delete('unit_test', $where));
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    /**
     * @covers ezsql\ezQuery::selecting
     * @covers ezsql\Database\ez_pdo::query
     * @covers ezsql\Database\ez_pdo::processQuery
     * @covers ezsql\Database\ez_pdo::processResult
     * @covers ezsql\Database\ez_pdo::prepareValues
     * @covers ezsql\Database\ez_pdo::query_prepared
     */
    public function testSelecting()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
        $this->object->insert('unit_test', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
        $this->object->insert('unit_test', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));

        $result = $this->object->selecting('unit_test');
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing string ' . $i, $row->test_value);
            $this->assertEquals('test ' . $i, $row->test_key);
            ++$i;
        }

        $where = eq('id', '2');
        $result = $this->object->selecting('unit_test', 'id', $this->object->where($where));
        foreach ($result as $row) {
            $this->assertEquals(2, $row->id);
        }

        $where = [eq('test_value', 'testing string 3', _AND), eq('id', '3')];
        $result = $this->object->selecting('unit_test', 'test_key', $this->object->where($where));
        foreach ($result as $row) {
            $this->assertEquals('test 3', $row->test_key);
        }

        $result = $this->object->selecting('unit_test', 'test_value', $this->object->where(eq('test_key', 'test 1')));
        foreach ($result as $row) {
            $this->assertEquals('testing string 1', $row->test_value);
        }
    }

    /**
     * @covers ezsql\Database\ez_pdo::disconnect
     */
    public function testPosgreSQLDisconnect()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->object->disconnect();

        $this->assertFalse($this->object->isConnected());
    } // testPosgreSQLDisconnect
} // ezsql\Database\ez_pdoTest
