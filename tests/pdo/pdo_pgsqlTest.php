<?php

namespace ezsql\Tests\pdo;

use ezsql\Tests\EZTestCase;

use function ezsql\functions\{
    pdoInstance,
    leftJoin,
    grouping,
    like,
    where,
    eq
};

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
     * @var \ezsql\Database\ez_pdo
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

        $this->object = pdoInstance(['pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
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

    public function testPosgreSQLConnect()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    } // testPosgreSQLConnect

    public function testPosgreSQLQuick_connect()
    {
        $this->assertTrue($this->object->quick_connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    } // testPosgreSQLQuick_connect

    public function testPosgreSQLEscape()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testPosgreSQLEscape

    public function testPosgreSQLSysDate()
    {
        $this->assertEquals("datetime('now')", $this->object->sysDate());
    } // testPosgreSQLSysDate

    public function testPosgreSQLCatch_error()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->object->query('DROP TABLE unit_test2');
        $this->assertTrue($this->object->catch_error());
    } // testPosgreSQLCatch_error

    public function testPosgreSQLQuery()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertEquals(0, $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'));

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } // testPosgreSQLQuery

    public function testInsert()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), PRIMARY KEY (ID))');

        $result = $this->object->insert('unit_test', array('test_key' => 'test 1'));
        $this->assertEquals(1, $result);

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

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

    public function testSelect()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->drop('unit_test');
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
        $this->object->insert('unit_test', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
        $this->object->insert('unit_test', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));

        $result = $this->object->select('unit_test');
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing string ' . $i, $row->test_value);
            $this->assertEquals('test ' . $i, $row->test_key);
            ++$i;
        }

        $where = eq('id', '2');
        $result = $this->object->select('unit_test', 'id', $this->object->where($where));
        foreach ($result as $row) {
            $this->assertEquals(2, $row->id);
        }

        $where = [eq('test_value', 'testing string 3', _AND), eq('id', '3')];
        $result = $this->object->select('unit_test', 'test_key', $this->object->where($where));
        foreach ($result as $row) {
            $this->assertEquals('test 3', $row->test_key);
        }

        $result = $this->object->select('unit_test', 'test_value', $this->object->where(eq('test_key', 'test 1')));
        foreach ($result as $row) {
            $this->assertEquals('testing string 1', $row->test_value);
        }

        $this->object->drop('unit_test');
    }

    public function testWhereGrouping()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->drop('unit_test_more');
        $this->object->query('CREATE TABLE unit_test_more(id serial, test_key varchar(50), active_data integer, PRIMARY KEY (ID))');
        $this->object->insert('unit_test_more', array('test_key' => 'testing 1', 'active_data' => 1));
        $this->object->insert('unit_test_more', array('test_key' => 'testing 2', 'active_data' => 0));
        $this->object->insert('unit_test_more', array('test_key' => 'testing 3', 'active_data' => 1));
        $this->object->insert('unit_test_more', array('test_key' => 'testing 4', 'active_data' => 1));

        $result = $this->object->select('unit_test_more', '*', where(eq('active_data', 1), grouping(like('test_key', '%1%', _OR), like('test_key', '%3%'))));
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            $i = $i + 2;
        }

        $this->object->drop('unit_test_more');
    }

    public function testJoins()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->drop('unit_test');
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('id' => '1', 'test_key' => 'testing 1'));
        $this->object->insert('unit_test', array('id' => '2', 'test_key' => 'testing 2'));
        $this->object->insert('unit_test', array('id' => '3', 'test_key' => 'testing 3'));
        $this->object->query('CREATE TABLE unit_test_child(child_id integer, child_test_key varchar(50), parent_id integer, PRIMARY KEY (child_id))');
        $this->object->insert('unit_test_child', array('child_id' => '1', 'child_test_key' => 'testing child 1', 'parent_id' => '3'));
        $this->object->insert('unit_test_child', array('child_id' => '2', 'child_test_key' => 'testing child 2', 'parent_id' => '2'));
        $this->object->insert('unit_test_child', array('child_id' => '3', 'child_test_key' => 'testing child 3', 'parent_id' => '1'));

        $result = $this->object->select('unit_test_child', '*', leftJoin('unit_test_child', 'unit_test', 'parent_id', 'id'));
        $i = 1;
        $o = 3;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->child_id);
            $this->assertEquals('testing child ' . $i, $row->child_test_key);
            $this->assertEquals($o, $row->id);
            $this->assertEquals('testing ' . $o, $row->test_key);
            ++$i;
            --$o;
        }

        $result = $this->object->select('unit_test_child', 'child.parent_id', leftJoin('unit_test_child', 'unit_test', 'parent_id', 'id', 'child'));
        $o = 3;
        foreach ($result as $row) {
            $this->assertEquals($o, $row->parent_id);
            --$o;
        }

        $this->object->drop('unit_test');
        $this->object->drop('unit_test_child');
    }

    public function testPosgreSQLDisconnect()
    {
        $this->assertTrue($this->object->connect('pgsql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->object->disconnect();

        $this->assertFalse($this->object->isConnected());
    } // testPosgreSQLDisconnect
} // ezsql\Database\ez_pdoTest
