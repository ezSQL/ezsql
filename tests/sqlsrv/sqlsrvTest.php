<?php

namespace ezsql\Tests\sqlsrv;

use ezsql\Config;
use ezsql\Database\ez_sqlsrv;
use ezsql\Tests\EZTestCase;

use function ezsql\functions\{
    column,
    primary,
    eq,
    mssqlInstance
};

class sqlsrvTest extends EZTestCase
{

    /**
     * @var ez_sqlsrv
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        if (!extension_loaded('sqlsrv')) {
            $this->markTestSkipped(
                'The sqlsrv Lib is not available.'
            );
        }

        $this->object = mssqlInstance([self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->object->prepareOn();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->object->drop('unit_test');
        $this->object = null;
    }

    public function testSettings()
    {
        $this->assertTrue($this->object->settings() instanceof \ezsql\ConfigInterface);
    }

    public function testQuick_connect()
    {
        $result = $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->assertTrue($result);
    }

    public function testConnect()
    {
        $result = $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->assertTrue($result);

        $result = $this->object->connect('self::TEST_DB_USER', 'self::TEST_DB_PASSWORD', 'self::TEST_DB_NAME');
        $this->assertFalse($result);
    }

    public function testEscape()
    {
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is\\'nt escaped.", $result);
    }

    public function testSysDate()
    {
        $this->assertEquals('GETDATE()', $this->object->sysDate());
    }

    public function testGet_var()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $current_time = $this->object->get_var("SELECT " . $this->object->sysDate() . " AS 'GetDate()'");
        $this->assertNotNull($current_time);
    }

    public function testGet_results()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);

        // Get list of tables from current database..
        $my_tables = $this->object->get_results("select name from " . self::TEST_DB_NAME . "..sysobjects where xtype = 'U'", ARRAY_N);
        $this->assertNotNull($my_tables);

        // Loop through each row of results..
        foreach ($my_tables as $table) {
            // Get results of DESC table..
            $this->assertNotNull($this->object->query("EXEC SP_COLUMNS '" . $table[0] . "'"));
        }
    }

    public function testQuery()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->assertEquals($this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'), 0);
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(1, \'test 1\')'), 1);

        $this->object->reset();
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(2, \'test 2\')'), 1);
        $this->object->disconnect();
        $this->assertFalse($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(3, \'test 3\')'));
    }

    public function testConvert()
    {
        $result = $this->object->convert("SELECT `test` FROM `unit_test`;");
        $this->assertEquals("SELECT test FROM unit_test;", $result);
    }

    public function testCreate()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);

        $this->assertEquals(
            $this->object->create(
                'new_create_test',
                column('id', AUTO),
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
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->assertEquals($this->object->drop('new_create_test'), 0);
    }

    public function testInsert()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');

        $this->assertNotFalse($this->object->insert('unit_test', ['id' => 7, 'test_key' => 'testInsert() 1']));
    }

    public function testUpdate()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->object->query('DROP TABLE unit_test');
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->assertNotFalse($this->object->insert('unit_test', array('id' => 1, 'test_key' => 'testUpdate() 1')));

        $this->object->insert('unit_test', array('id' => 2, 'test_key' => 'testUpdate() 2'));
        $this->object->insert('unit_test', array('id' => 3, 'test_key' => 'testUpdate() 3'));

        $unit_test['test_key'] = 'testing';
        $where = eq('id', 1);
        $this->assertEquals(
            1,
            $this->object->update('unit_test', $unit_test, $where)
        );

        $this->assertEquals(
            1,
            $this->object->update(
                'unit_test',
                $unit_test,
                eq('id', 3),
                eq('test_key', 'testUpdate() 3')
            )
        );

        $this->assertEquals(
            0,
            $this->object->update('unit_test', $unit_test, eq('id', 4))
        );

        $this->assertEquals(
            1,
            $this->object->update('unit_test', $unit_test, eq('test_key', 'testUpdate() 2'), eq('id', 2))
        );
    }

    public function testDelete()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
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
        $this->assertFalse($this->object->delete('unit_test', array('test_key', '=', $where)));

        $where = eq('id', 2);
        $this->assertEquals(
            1,
            $this->object->delete('unit_test', $where)
        );
    }

    public function testSelect()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
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

        $result = $this->object->select('unit_test', array('test_key'), eq('id', 8));
        foreach ($result as $row) {
            $this->assertEquals('testing 8', $row->test_key);
        }
    }

    public function testBeginTransactionCommit()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');

        $commit = null;
        try {
            $commit = true;
            $this->object->beginTransaction();
            $this->object->insert('unit_test', array('id' => 8, 'test_key' => 'testing 8'));
            $this->object->insert('unit_test', array('id' => 9, 'test_key' => 'testing 9'));
            $this->object->insert('unit_test', array('id' => 10, 'test_key' => 'testing 10'));
            $this->object->commit();
        } catch (\Exception $ex) {
            $commit = false;
            $this->object->rollback();
            echo ("Error! This rollback message shouldn't have been displayed: ") . $ex->getMessage();
        }

        if ($commit) {
            $result = $this->object->select('unit_test');
            $i = 8;

            foreach ($result as $row) {
                $this->assertEquals($i, $row->id);
                $this->assertEquals('testing ' . $i, $row->test_key);
                ++$i;
            }

            $this->object->drop('unit_test');
        }
    }

    public function testBeginTransactionRollback()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');

        $commit = null;
        try {
            $commit = true;
            $this->object->beginTransaction();
            $this->object->insert('unit_test', array('id' => 8, 'test_key' => 'testing 8'));
            $this->object->insert('unit_test', array('id' => 9, 'test_key' => 'testing 9'));
            $this->object->insert('unit_test', array('idx' => 10, 'test_key' => 'testing 10'));
            $this->object->commit();
        } catch (\Exception $ex) {
            $commit = false;
            $this->object->rollback();
        }

        if ($commit) {
            echo ("Error! This message shouldn't have been displayed.");
            $result = $this->object->select('unit_test');
            $i = 8;

            foreach ($result as $row) {
                $this->assertEquals($i, $row->id);
                $this->assertEquals('should not be seen ' . $i, $row->test_key);
                ++$i;
            }

            $this->object->drop('unit_test');
        } else {
            //echo ("Error! rollback.");
            $result = $this->object->select('unit_test');
            $i = 8;

            foreach ($result as $row) {
                $this->assertEquals($i, $row->id);
                $this->assertEquals('should not be seen ' . $i, $row->test_key);
                ++$i;
            }

            $this->assertEquals(0, $result);
            $this->object->drop('test_table');
        }
    }

    public function testDisconnect()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->assertTrue($this->object->isConnected());
        $this->assertNotNull($this->object->handle());
        $this->object->disconnect();
        $this->assertFalse($this->object->isConnected());
        $this->object->reset();
        $this->assertNull($this->object->handle());
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->assertTrue($this->object->isConnected());
    }

    public function testQuery_prepared()
    {
        $this->object->prepareOff();
        $this->object->create(
            'prepare_test',
            column('id', INTEGERS, PRIMARY),
            column('prepare_key', VARCHAR, 50)
        );

        $this->object->query_prepared('INSERT INTO prepare_test(id, prepare_key ) VALUES( ?, ? )', [1, 'test 2']);
        $this->object->query_prepared('INSERT INTO prepare_test(id, prepare_key ) VALUES( ?, ? )', [4, 'test 10']);
        $this->object->query_prepared('INSERT INTO prepare_test(id, prepare_key ) VALUES( ?, ? )', [9, 'test 3']);

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
        $this->assertNull(new ez_sqlsrv());
    }

    public function test__construct()
    {
        unset($GLOBALS['ez' . \SQLSRV]);
        $settings = Config::initialize('sqlsrv', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->assertNotNull(new ez_sqlsrv($settings));
    }
}
