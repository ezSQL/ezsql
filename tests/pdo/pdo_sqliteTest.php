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

class pdo_sqliteTest extends EZTestCase
{
    /**
     * constant string database port
     */
    const TEST_DB_PORT = '5432';
    /**
     * constant string path and file name of the SQLite test database
     */
    const TEST_SQLITE_DB = './tests/pdo/ez_test.sqlite';

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
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped(
                'The pdo_sqlite Lib is not available.'
            );
        }

        $this->object = pdoInstance(['sqlite:' . self::TEST_SQLITE_DB, '', '', array(), true]);
        $this->object->prepareOn();
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->object->drop('unit_test');
        $this->object = null;
    } // tearDown

    /**
     * Here starts the SQLite PDO unit test
     */
    public function testSQLiteConnect()
    {
        $this->assertTrue($this->object->connect());
        $this->assertTrue($this->object->connect(null));
        $this->assertFalse($this->object->connect('ccc', 'vccc'));
    } // testSQLiteConnect

    public function testSQLiteQuick_connect()
    {
        $this->assertTrue($this->object->quick_connect('sqlite:' . self::TEST_SQLITE_DB, '', '', array(), true));
    } // testSQLiteQuick_connect

    public function testSQLiteEscape()
    {
        $this->assertTrue($this->object->connect('sqlite:' . self::TEST_SQLITE_DB, '', '', array(), true));

        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);

        $this->object->disconnect();
        $result = $this->object->escape("Is'nt escaped.");
        $this->assertEquals("Is\'nt escaped.", $result);
    } // testSQLiteEscape

    public function testSQLiteSysdate()
    {
        $this->assertEquals("datetime('now')", $this->object->sysdate());
    } // testSQLiteSysdate

    public function testSQLiteCatch_error()
    {
        $this->assertTrue($this->object->connect('sqlite:' . self::TEST_SQLITE_DB, '', '', array(), true));
        $this->object->query('DROP TABLE unit_test2');
        $this->assertTrue($this->object->catch_error());
    } // testSQLiteCatch_error

    public function testSQLiteQuery()
    {
        $this->assertTrue($this->object->connect());

        $this->assertEquals(0, $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'));

        $result = $this->object->query('INSERT INTO unit_test (id, test_key) VALUES (1, \'test 1\');');
        $this->assertEquals(1, $result);
        $this->assertNull($this->object->catch_error());

        $this->object->query('INSERT INTO unit_test (id, test_key2) VALUES (1, \'test 1\');');
        $this->assertTrue($this->object->catch_error());

        $this->object->disconnect();
        $result = $this->object->query('INSERT INTO unit_test (id, test_key) VALUES (5, \'test 5\');');
        $this->assertEquals(1, $result);
        $this->assertNull($this->object->catch_error());

        $this->object->setUse_Trace_Log(true);
        $this->assertNotNull($this->object->query('SELECT * FROM unit_test ;'));
        $this->assertNotNull($this->object->getTrace_Log());

        $this->assertFalse($this->object->query('SELECT id2 FROM unit_test ;'));
        $this->assertTrue($this->object->catch_error());

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } // testSQLiteQuery

    public function testInsert()
    {
        $this->assertTrue($this->object->connect('sqlite:' . self::TEST_SQLITE_DB, '', '', array(), true));
        $this->assertEquals(0, $this->object->drop('unit_test'));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');

        $result = $this->object->insert('unit_test', array('test_key' => 'test 1'));
        $this->assertEquals(1, $result);
    }

    public function testUpdate()
    {
        $this->assertTrue($this->object->connect('sqlite:' . self::TEST_SQLITE_DB, '', '', array(), true));
        $this->assertEquals(0, $this->object->drop('unit_test'));

        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');

        $this->object->insert('unit_test', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
        $this->object->insert('unit_test', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
        $result = $this->object->insert('unit_test', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));
        $this->assertEquals($result, 3);

        $unit_test['test_key'] = 'the key string';
        $where = eq('test_key', 'test 1');
        $this->assertEquals(1, $this->object->update('unit_test', $unit_test, $where));
        $this->assertEquals(1, $this->object->update(
            'unit_test',
            $unit_test,
            eq('test_key', 'test 3'),
            eq('test_value', 'testing string 3')
        ));

        $where = eq('test_value', 'testing string 4');
        $this->assertEquals(0, $this->object->update('unit_test', $unit_test, $where));

        $this->assertEquals(1, $this->object->update('unit_test', $unit_test, eq('test_key', 'test 2')));
        $this->assertEquals(1, $this->object->query('DROP TABLE unit_test'));
    }

    public function testDelete()
    {
        $this->assertTrue($this->object->connect('sqlite:' . self::TEST_SQLITE_DB, '', '', array(), true));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
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

        $this->assertEquals(1, $this->object->query('DROP TABLE unit_test'));
    }

    public function testSelect()
    {
        $this->assertTrue($this->object->connect('sqlite:' . self::TEST_SQLITE_DB, '', '', array(), true));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
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
        $this->assertEquals(1, $this->object->query('DROP TABLE unit_test'));
    }

    public function testWhereGrouping()
    {
        $this->assertTrue($this->object->connect('sqlite:' . self::TEST_SQLITE_DB, '', '', array(), true));
        $this->object->drop('unit_test');
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), active tinyint(1), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('id' => '1', 'test_key' => 'testing 1', 'active' => 1));
        $this->object->insert('unit_test', array('id' => '2', 'test_key' => 'testing 2', 'active' => 0));
        $this->object->insert('unit_test', array('id' => '3', 'test_key' => 'testing 3', 'active' => 1));
        $this->object->insert('unit_test', array('id' => '4', 'test_key' => 'testing 4', 'active' => 1));

        $result = $this->object->select('unit_test', '*', where(eq('active', '1'), grouping(like('test_key', '%1%', _OR), like('test_key', '%3%'))));
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            $i = $i + 2;
        }

        $this->assertEquals(1, $this->object->query('DROP TABLE unit_test'));
    }

    public function testJoins()
    {
        $this->assertTrue($this->object->connect('sqlite:' . self::TEST_SQLITE_DB, '', '', array(), true));
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

        $this->assertEquals(1, $this->object->query('DROP TABLE unit_test'));
        $this->assertEquals(1, $this->object->query('DROP TABLE unit_test_child'));
    }

    public function testSQLiteDisconnect()
    {
        $this->assertTrue($this->object->connect());

        $this->object->disconnect();

        $this->assertFalse($this->object->isConnected());
    } // testSQLiteDisconnect
} // ezsql\Database\ez_pdoTest
