<?php

namespace ezsql\Tests\postgresql;

use ezsql\Config;
use ezsql\Database\ez_pgsql;
use ezsql\Tests\EZTestCase;

use function ezsql\functions\{
    column,
    creating,
    deleting,
    dropping,
    updating,
    primary,
    eq,
    pgsqlInstance,
    selecting,
    inserting,
    table_setup,
    where
};

class postgresqlTest extends EZTestCase
{
    /**
     * constant database port
     */
    const TEST_DB_PORT = '5432';

    /**
     * @var ez_pgsql
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        if (!extension_loaded('pgsql')) {
            $this->markTestSkipped(
                'The PostgreSQL Lib is not available.'
            );
        }

        $this->object = pgsqlInstance([self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT]);
        $this->object->prepareOn();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->object = null;
    }

    public function testSettings()
    {
        $this->assertTrue($this->object->settings() instanceof \ezsql\ConfigInterface);
    }

    public function testQuick_connect()
    {
        $this->assertTrue($this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
    }

    public function testConnect()
    {
        $this->errors = array();
        set_error_handler(array($this, 'errorHandler'));

        $this->assertFalse($this->object->connect('self::TEST_DB_USER', 'self::TEST_DB_PASSWORD', 'self::TEST_DB_NAME', 'self::TEST_DB_CHARSET'));

        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));

        $this->assertTrue($this->object->connect());
    }

    public function testEscape()
    {
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    }

    public function testSysDate()
    {
        $this->assertEquals('NOW()', $this->object->sysDate());
    }

    public function testQuery()
    {
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));

        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $this->assertEquals($this->object->query('INSERT INTO unit_test(test_key, test_value) VALUES(\'test 1\', \'testing string 1\')'), 1);

        $this->object->reset();
        $this->assertNotNull($this->object->query('INSERT INTO unit_test(test_key, test_value) VALUES(\'test 2\', \'testing string 2\')'));
        $this->object->disconnect();
        $this->assertFalse($this->object->query('INSERT INTO unit_test(test_key, test_value) VALUES(\'test 3\', \'testing string 3\')'));

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    public function testCreate()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);

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
            1
        );
        $this->object->prepareOn();
    }

    public function testDrop()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);

        $this->assertEquals($this->object->drop('new_create_test'), 0);
    }

    public function testInsert()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $result = $this->object->insert('unit_test', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
        $this->assertEquals($result, 1);
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    public function testUpdate()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
        $this->object->insert('unit_test', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
        $result = $this->object->insert('unit_test', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));
        $this->assertEquals($result, 3);

        $unit_test['test_key'] = 'the key string';
        $where = eq('test_key', 'test 1');
        $this->assertEquals(
            1,
            $this->object->update('unit_test', $unit_test, $where)
        );

        $this->assertEquals(1, $this->object->update(
            'unit_test',
            $unit_test,
            array('test_key', EQ, 'test 3', 'and'),
            array('test_value', '=', 'testing string 3')
        ));

        $where = array('test_value', EQ, 'testing string 4');
        $this->assertEquals(
            0,
            $this->object->update('unit_test', $unit_test, $where)
        );

        $this->assertEquals(
            1,
            $this->object->update('unit_test', $unit_test, eq('test_key', 'test 2'))
        );

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    public function testUpdatingDeleting()
    {
        $this->object->prepareOff();
        $this->object->drop('unit_test');
        $this->assertFalse($this->object->updating([]));
        $this->assertFalse($this->object->deleting([]));
        $this->assertFalse($this->object->inserting([]));
        $this->assertFalse($this->object->selecting());

        table_setup('unit_test');
        $this->assertEquals(
            0,
            creating(
                column('id', AUTO, PRIMARY),
                column('test_key', VARCHAR, 50),
                column('test_value', VARCHAR, 50)
            )
        );

        inserting(array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
        inserting(array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
        $result = inserting(array('test_key' => 'test 3', 'test_value' => 'testing string 3'));

        $this->assertEquals($result, 3);

        $unit_test['test_key'] = 'the key string';
        $this->assertEquals(1, updating($unit_test, eq('test_key', 'test 1')));
        $this->assertEquals(1, deleting(eq('test_key', 'test 3')));

        $result = selecting('test_value', eq('test_key', 'the key string'));
        foreach ($result as $row) {
            $this->assertEquals('testing string 1', $row->test_value);
        }

        dropping();
    }

    public function testDelete()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
        $this->object->insert('unit_test', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
        $this->object->insert('unit_test', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));

        $where = array('test_key', '=', 'test 1');
        $this->assertEquals($this->object->delete('unit_test', $where), 1);

        $this->assertEquals(1, $this->object->delete(
            'unit_test',
            array('test_key', '=', 'test 3'),
            array('test_value', '=', 'testing string 3')
        ));

        $where = array('test_value', '=', 'testing 2');
        $this->assertEquals(
            0,
            $this->object->delete('unit_test', $where)
        );

        $where = eq('test_key', 'test 2');
        $this->assertEquals(
            1,
            $this->object->delete('unit_test', $where)
        );

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    public function testDisconnect()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
        $this->assertTrue($this->object->isConnected());
        $this->assertNotNull($this->object->handle());
        $this->object->disconnect();
        $this->assertFalse($this->object->isConnected());
        $this->object->reset();
        $this->assertNull($this->object->handle());
    }

    public function testGetHost()
    {
        $this->assertEquals(self::TEST_DB_HOST, $this->object->getHost());
    }

    public function testGetPort()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);

        $this->assertEquals(self::TEST_DB_PORT, $this->object->getPort());
    }

    public function testSelect()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
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

        $result = $this->object->select('unit_test', 'test_value', where(eq('test_key', 'test 1')));
        foreach ($result as $row) {
            $this->assertEquals('testing string 1', $row->test_value);
        }
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    public function testBeginTransactionCommit()
    {
        $this->object->connect();
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');

        $commit = null;
        try {
            $commit = true;
            $this->object->beginTransaction();
            $this->object->insert('unit_test', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
            $this->object->insert('unit_test', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
            $this->object->insert('unit_test', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));
            $this->object->commit();
        } catch (\Exception $ex) {
            $commit = false;
            $this->object->rollback();
            echo ("Error! This rollback message shouldn't have been displayed: ") . $ex->getMessage();
        }

        if ($commit) {
            $result = $this->object->select('unit_test');
            $i = 1;
            foreach ($result as $row) {
                $this->assertEquals($i, $row->id);
                $this->assertEquals('testing string ' . $i, $row->test_value);
                $this->assertEquals('test ' . $i, $row->test_key);
                ++$i;
            }

            $this->assertEquals(0, $this->object->drop('unit_test'));
        }
    }

    public function testBeginTransactionRollback()
    {
        $this->object->connect();
        $this->object->query('CREATE TABLE unit_test(id int(11) NOT NULL AUTO_INCREMENT, test_key varchar(50), PRIMARY KEY (ID)');

        $commit = null;
        try {
            $commit = true;
            $this->object->beginTransaction();
            $this->object->insert('unit_test', array('test_key' => 'test 1', 'test_value' => 'testing string 1'));
            $this->object->insert('unit_test', array('test_key' => 'test 2', 'test_value' => 'testing string 2'));
            $this->object->insert('unit_test', array('test_key' => 'test 3', 'test_value' => 'testing string 3'));
            $this->object->commit();
        } catch (\Exception $ex) {
            $commit = false;
            $this->object->rollback();
        }

        if ($commit) {
            echo ("Error! This message shouldn't have been displayed.");
            $result = $this->object->select('unit_test');
            $i = 1;
            foreach ($result as $row) {
                $this->assertEquals($i, $row->id);
                $this->assertEquals('should not be seen ' . $i, $row->test_value);
                $this->assertEquals('test ' . $i, $row->test_key);
                ++$i;
            }

            $this->object->drop('unit_test');
        } else {
            //echo ("Error! rollback.");
            $result = $this->object->select('unit_test');
            $i = 1;
            foreach ($result as $row) {
                $this->assertEquals($i, $row->id);
                $this->assertEquals('should not be seen ' . $i, $row->test_value);
                $this->assertEquals('test ' . $i, $row->test_key);
                ++$i;
            }

            $this->assertEquals(0, $result);
            $this->object->drop('unit_test');
        }
    }

    public function testQuery_prepared()
    {
        $this->object->prepareOff();
        $this->object->connect();
        $this->object->drop('prepare_test');
        $this->assertEquals(
            0,
            $this->object->create(
                'prepare_test',
                column('id', AUTO, PRIMARY),
                column('prepare_key', VARCHAR, 50)
            )
        );

        $this->object->insert('prepare_test', array('prepare_key' => 'test 1'));
        $result = $this->object->insert('prepare_test', array('prepare_key' => 'test 2'));
        $this->assertEquals(2, $result);

        $this->object->query_prepared('SELECT id, prepare_key FROM prepare_test WHERE id = $1', [1]);

        $query = $this->object->queryResult();
        foreach ($query as $row) {
            $this->assertEquals(1, $row->id);
            $this->assertEquals('test 1', $row->prepare_key);
        }

        $this->object->drop('prepare_test');
    }

    public function test__Construct_Error()
    {
        $this->expectExceptionMessageRegExp('/[Missing configuration details]/');
        $this->assertNull(new ez_pgsql());
    }

    public function test__construct()
    {
        unset($GLOBALS['ez' . \PGSQL]);
        $settings = new Config('pgsql', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT]);
        $this->assertNotNull(new ez_pgsql($settings));
    }
}
