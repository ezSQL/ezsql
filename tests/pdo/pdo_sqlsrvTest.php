<?php

namespace ezsql\Tests\pdo;

use ezsql\Tests\EZTestCase;

use function ezsql\functions\{
    grouping,
    where,
    eq,
    like,
    leftJoin,
    pdoInstance
};

class pdo_sqlsrvTest extends EZTestCase
{
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
        if (!extension_loaded('pdo_sqlsrv')) {
            $this->markTestSkipped(
                'The pdo_sqlsrv Lib is not available.'
            );
        }

        $this->object = pdoInstance(['sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
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

    public function testSQLsrvConnect()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    } // testSQLsrvConnect

    public function testSQLsrvQuick_connect()
    {
        $this->assertTrue($this->object->quick_connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    } // testSQLsrvQuick_connect

    public function testSQLsrvEscape()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testSQLsrvEscape

    public function testSQLsrvSysdate()
    {
        $this->assertEquals("datetime('now')", $this->object->sysdate());
    } // testSQLsrvSysdate

    public function testSQLsrvCatch_error()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->object->query('DROP TABLE unit_test2');
        $this->assertTrue($this->object->catch_error());
    } // testSQLsrvCatch_error

    public function testSQLsrvQuery()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertEquals(0, $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'));

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } // testSQLsrvQuery

    public function testInsert()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->assertNotFalse($this->object->insert('unit_test', ['id' => 7, 'test_key' => 'testInsert() 1']));
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    public function testUpdate()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->drop('unit_test');
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->assertNotFalse($this->object->insert('unit_test', array('id' => 1, 'test_key' => 'testUpdate() 1')));
        $this->object->insert('unit_test', array('id' => 2, 'test_key' => 'testUpdate() 2'));
        $this->object->insert('unit_test', array('id' => 3, 'test_key' => 'testUpdate() 3'));

        $unit_test['test_key'] = 'testing';
        $where = ['id', '=', 1];
        $this->assertEquals(
            1,
            $this->object->update('unit_test', $unit_test, $where)
        );

        $this->assertEquals(
            1,
            $this->object->update('unit_test', $unit_test, eq('id', 3), eq('test_key', 'testUpdate() 3'))
        );

        $this->assertEquals(
            0,
            $this->object->update('unit_test', $unit_test, ['id', '=', 4])
        );

        $this->assertEquals(
            1,
            $this->object->update('unit_test', $unit_test, ['test_key ', '=', 'testUpdate() 2'], eq('id', 2))
        );
    }

    public function testDelete()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');

        $unit_test['id'] = 1;
        $unit_test['test_key'] = 'testDelete() 1';
        $this->object->insert('unit_test', $unit_test);

        $unit_test['id'] = 2;
        $unit_test['test_key'] = 'testDelete() 2';
        $this->object->insert('unit_test', $unit_test);

        $unit_test['id'] = 3;
        $unit_test['test_key'] = 'testDelete() 3';
        $this->object->insert('unit_test', $unit_test);

        $this->assertEquals(
            1,
            $this->object->delete('unit_test', ['id', '=', 1])
        );

        $this->assertEquals(
            1,
            $this->object->delete('unit_test', eq('id', 3), eq('test_key', 'testDelete() 3'))
        );

        $where = 1;
        $this->assertEquals(
            0,
            $this->object->delete('unit_test', array('test_key', '=', $where))
        );

        $where = ['id', '=', 2];
        $this->assertEquals(
            1,
            $this->object->delete('unit_test', $where)
        );
    }

    public function testSelect()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->drop('unit_test');
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('id' => 8, 'test_key' => 'testing 8'));
        $this->object->insert('unit_test', array('id' => 9, 'test_key' => 'testing 9'));
        $this->object->insert('unit_test', array('id' => 10, 'test_key' => 'testing 10'));

        $result = $this->object->select('unit_test');
        $i = 8;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            ++$i;
        }

        $where = eq('test_key', 'testing 10');
        $result = $this->object->select('unit_test', 'id', $where);
        foreach ($result as $row) {
            $this->assertEquals(10, $row->id);
        }

        $result = $this->object->select('unit_test', 'test_key', eq('id', 9));
        foreach ($result as $row) {
            $this->assertEquals('testing 9', $row->test_key);
        }

        $result = $this->object->select('unit_test', array('test_key'), ['id', '=', 8]);
        foreach ($result as $row) {
            $this->assertEquals('testing 8', $row->test_key);
        }

        $this->object->drop('unit_test');
    }

    public function testWhereGrouping()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test_other(id integer, test_key varchar(50), active_data integer, PRIMARY KEY (ID))');
        $this->object->insert('unit_test_other', array('id' => 1, 'test_key' => 'testing 1', 'active_data' => 1));
        $this->object->insert('unit_test_other', array('id' => 2, 'test_key' => 'testing 2', 'active_data' => 0));
        $this->object->insert('unit_test_other', array('id' => 3, 'test_key' => 'testing 3', 'active_data' => 1));
        $this->object->insert('unit_test_other', array('id' => 4, 'test_key' => 'testing 4', 'active_data' => 1));

        $result = $this->object->select('unit_test_other', '*', where(eq('active_data', '1'), grouping(like('test_key', '%1%', _OR), like('test_key', '%3%'))));
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            $i = $i + 2;
        }

        $this->object->drop('unit_test_other');
    }

    public function testJoins()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
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

    public function testSQLsrvDisconnect()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->object->disconnect();

        $this->assertFalse($this->object->isConnected());
    } // testSQLsrvDisconnect
} // ezsql\Database\ez_pdoTest
