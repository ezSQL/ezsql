<?php

namespace ezsql\Tests\sqlite;

use Exception;
use ezsql\Config;
use ezsql\Database\ez_sqlite3;
use ezsql\Tests\EZTestCase;

use function ezsql\functions\{
    column,
    primary,
    eq,
    sqliteInstance,
};

/**
 * Generated by PHPUnit_SkeletonGenerator on 2018-03-08 at 02:54:12.
 */
class sqlite3Test extends EZTestCase
{
    /**
     * constant string path and file name of the SQLite test database
     */
    const TEST_SQLITE_DB = 'ez_test.sqlite3';
    const TEST_SQLITE_DB_DIR = './tests/sqlite/';

    /**
     * @var ez_sqlite3
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        if (!extension_loaded('sqlite3')) {
            $this->markTestSkipped(
                'The sqlite3 Lib is not available.'
            );
        }

        $this->object = sqliteInstance([self::TEST_SQLITE_DB_DIR, self::TEST_SQLITE_DB]);
        $this->object->prepareOn();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->object->drop("test_table");
        $this->object = null;
    }

    public function testSettings()
    {
        $this->assertTrue($this->object->settings() instanceof \ezsql\ConfigInterface);
    }

    public function testDisconnect()
    {
        $this->object->connect();
        $this->assertTrue($this->object->isConnected());
        $this->assertNotNull($this->object->handle());
        $this->object->disconnect();
        $this->assertFalse($this->object->isConnected());
        $this->object->reset();
        $this->assertNull($this->object->handle());
    }

    public function testConnect()
    {
        $this->assertTrue($this->object->connect());
        $this->assertTrue($this->object->isConnected());
    }

    public function testQuick_connect()
    {
        $this->assertNotNull($this->object->quick_connect(self::TEST_SQLITE_DB_DIR, self::TEST_SQLITE_DB));
    }

    public function testSQLite3Escape()
    {
        $this->object->connect(self::TEST_SQLITE_DB_DIR, self::TEST_SQLITE_DB);
        $result = $this->object->escape("This is'nt escaped.");
        $this->assertEquals("This is''nt escaped.", $result);
    }

    public function testSysDate()
    {
        $this->assertEquals('now', $this->object->sysDate());
    }

    public function testQuery()
    {
        $this->object->connect(self::TEST_SQLITE_DB_DIR, self::TEST_SQLITE_DB);
        // Create a table..
        $this->object->drop("test_table");
        $this->assertEquals(0, $this->object->query("CREATE TABLE test_table ( MyColumnA INTEGER PRIMARY KEY, MyColumnB TEXT(32) );"));

        // Insert test data
        for ($i = 0; $i < 3; ++$i) {
            $this->assertNotNull($this->object->query('INSERT INTO test_table (MyColumnB) VALUES ("' . md5(microtime()) . '");'));
        }

        // Get list of tables from current database..
        $my_tables = $this->object->get_results("SELECT * FROM sqlite_master WHERE sql NOTNULL;");

        // Loop through each row of results..
        foreach ($my_tables as $table) {
            // Get results of DESC table..
            $this->assertNotNull($this->object->get_results("SELECT * FROM $table->name;"));
        }

        // Get rid of the table we created..
        $this->object->query("DROP TABLE test_table;");
    }

    public function testCreate()
    {
        $this->object->connect(self::TEST_SQLITE_DB_DIR, self::TEST_SQLITE_DB);
        $this->assertEquals(
            $this->object->create(
                'new_create_test',
                column('id', INTEGERS, notNULL, AUTO),
                column('create_key', VARCHAR, 50),
                primary('id_pk', 'id')
            ),
            0
        );

        $this->object->prepareOff();
        $this->assertEquals(
            $this->object->insert(
                'new_create_test',
                ['create_key' => 'test 2']
            ),
            0
        );
        $this->object->prepareOn();
    }

    public function testDrop()
    {
        $this->assertEquals($this->object->drop('new_create_test'), 0);
    }

    public function testInsert()
    {
        $this->object->query('CREATE TABLE test_table(id integer, test_key varchar(50), PRIMARY KEY (ID))');

        $result = $this->object->insert('test_table', array('test_key' => 'test 1'));
        $this->assertEquals(0, $result);
    }

    public function testUpdate()
    {
        $this->object->query('CREATE TABLE test_table(id integer, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');

        $this->object->insert('test_table', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
        $this->object->insert('test_table', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
        $result = $this->object->insert('test_table', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));
        $this->assertEquals($result, 3);

        $test_table['test_key'] = 'the key string';
        $where = ['test_key', '=', 'test 1'];
        $this->assertEquals(
            1,
            $this->object->update('test_table', $test_table, $where)
        );

        $this->assertEquals(
            1,
            $this->object->update(
                'test_table',
                $test_table,
                eq('test_key', 'test 3'),
                eq('test_value', 'testing string 3')
            )
        );

        $where = eq('test_value', 'testing string 4');
        $this->assertEquals(
            0,
            $this->object->update('test_table', $test_table, $where)
        );

        $this->assertEquals(
            1,
            $this->object->update('test_table', $test_table, ['test_key', '=', 'test 2'])
        );
    }

    public function testDelete()
    {
        $this->object->query('CREATE TABLE test_table(id integer, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');

        $this->object->insert('test_table', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
        $this->object->insert('test_table', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
        $this->object->insert('test_table', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));

        $where = array('test_key', '=', 'test 1');
        $this->assertEquals($this->object->delete('test_table', $where), 1);

        $this->assertEquals($this->object->delete(
            'test_table',
            array('test_key', '=', 'test 3'),
            array('test_value', '=', 'testing string 3')
        ), 1);

        $where = array('test_value', '=', 'testing 2');
        $this->assertEquals(0, $this->object->delete('test_table', $where));

        $where = ['test_key', '=', 'test 2'];
        $this->assertEquals(1, $this->object->delete('test_table', $where));
    }

    public function testSelecting()
    {
        $this->object->query('CREATE TABLE test_table(id integer, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');

        $this->object->insert('test_table', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
        $this->object->insert('test_table', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
        $this->object->insert('test_table', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));

        $result = $this->object->selecting('test_table');

        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing string ' . $i, $row->test_value);
            $this->assertEquals('test ' . $i, $row->test_key);
            ++$i;
        }

        $where = eq('id', 2);
        $result = $this->object->selecting('test_table', 'id', $this->object->where($where));
        foreach ($result as $row) {
            $this->assertEquals(2, $row->id);
        }

        $where = [eq('test_value', 'testing string 3')];
        $result = $this->object->selecting('test_table', 'test_key', $this->object->where($where));
        foreach ($result as $row) {
            $this->assertEquals('test 3', $row->test_key);
        }

        $result = $this->object->selecting('test_table', 'test_value', $this->object->where(eq('test_key', 'test 1')));
        foreach ($result as $row) {
            $this->assertEquals('testing string 1', $row->test_value);
        }
    }

    public function testBeginTransactionCommit()
    {
        $this->object->connect();
        $this->object->query('CREATE TABLE IF NOT EXISTS test_table(id integer, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');

        $commit = null;
        try {
            $commit = true;
            $this->object->beginTransaction();
            $this->object->insert('test_table', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
            $this->object->insert('test_table', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
            $this->object->insert('test_table', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));
            $this->object->commit();
        } catch (\Exception $ex) {
            $commit = false;
            $this->object->rollback();
            echo ("Error! This rollback message shouldn't have been displayed: ") . $ex->getMessage();
        }

        if ($commit) {
            $result = $this->object->selecting('test_table');
            $i = 1;
            foreach ($result as $row) {
                $this->assertEquals($i, $row->id);
                $this->assertEquals('testing string ' . $i, $row->test_value);
                $this->assertEquals('test ' . $i, $row->test_key);
                ++$i;
            }

            $this->assertEquals(0, $this->object->drop('test_table'));
        }
    }

    public function testBeginTransactionRollback()
    {
        $this->object->query('CREATE TABLE IF NOT EXISTS test_table(id integer, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');

        $commit = null;
        try {
            $commit = true;
            $this->object->beginTransaction();
            $this->object->insert('test_table', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
            $this->object->insert('test_table', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
            $this->object->insert('test_table', array('test_keyx' => 'test 3', 'test_valuex' => 'testing string 3'));
            $this->object->commit();
        } catch (\Exception $ex) {
            $commit = false;
            $this->object->rollback();
        }

        if ($commit) {
            echo ("Error! This message shouldn't have been displayed.");
            $result = $this->object->selecting('test_table');

            $i = 1;
            foreach ($result as $row) {
                $this->assertEquals($i, $row->id);
                $this->assertEquals('should not be seen ' . $i, $row->test_value);
                $this->assertEquals('test ' . $i, $row->test_key);
                ++$i;
            }

            $this->object->drop('test_table');
        } else {
            //echo ("Error! rollback.");
            $result = $this->object->selecting('test_table');

            $i = 1;
            foreach ($result as $row) {
                $this->assertEquals($i, $row->id);
                $this->assertEquals('should not be seen ' . $i, $row->test_value);
                $this->assertEquals('test ' . $i, $row->test_key);
                ++$i;
            }

            $this->assertEquals(0, $result);
            $this->object->drop('test_table');
        }
    }

    public function testQuery_prepared()
    {
        $this->object->prepareOff();
        $this->object->drop('prepare_test');

        $this->object->create(
            'prepare_test',
            column('id', INTEGERS, PRIMARY),
            column('prepare_key', VARCHAR, 50)
        );

        $this->object->insert('prepare_test', ['id' => 1, 'prepare_key' => 'test 2']);
        $this->object->query_prepared('INSERT INTO prepare_test( id, prepare_key ) VALUES( ?, ? )', [4, 'test 10']);
        $this->object->query_prepared('INSERT INTO prepare_test( id, prepare_key ) VALUES( ?, ? )', [9, 'test 3']);

        $this->object->query_prepared('SELECT id, prepare_key FROM prepare_test WHERE id = ?', [9]);
        $query = $this->object->queryResult();
        foreach ($query as $row) {
            $this->assertEquals(9, $row->id);
            $this->assertEquals('test 3', $row->prepare_key);
        }

        $this->object->query_prepared('SELECT id, prepare_key FROM prepare_test WHERE id = ?', [1]);
        $query = $this->object->queryResult();
        foreach ($query as $row) {
            $this->assertEquals(1, $row->id);
            $this->assertEquals('test 2', $row->prepare_key);
        }

        $this->object->query_prepared('SELECT id, prepare_key FROM prepare_test WHERE id = ?', [4]);
        $query = $this->object->queryResult();
        foreach ($query as $row) {
            $this->assertEquals(4, $row->id);
            $this->assertEquals('test 10', $row->prepare_key);
        }

        $this->object->drop('prepare_test');
    }

    public function test__Construct_Error()
    {
        $this->expectExceptionMessageRegExp('/[Missing configuration details]/');
        $this->assertNull(new ez_sqlite3());
    }

    public function test__construct()
    {
        unset($GLOBALS['ez' . \SQLITE3]);
        $settings = new Config('sqlite3', [self::TEST_SQLITE_DB_DIR, self::TEST_SQLITE_DB]);
        $this->assertNotNull(new ez_sqlite3($settings));
    }
}
