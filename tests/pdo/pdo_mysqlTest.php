<?php

namespace ezsql\Tests\pdo;

use ezsql\Config;
use ezsql\Database\ez_pdo;
use ezsql\Tests\EZTestCase;

use function ezsql\functions\{
    pdoInstance,
    leftJoin,
    column,
    primary,
    grouping,
    like,
    where,
    eq
};

class pdo_mysqlTest extends EZTestCase
{

    /**
     * constant string database port
     */
    const TEST_DB_PORT = '3306';

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
        if (!extension_loaded('pdo_mysql')) {
            $this->markTestSkipped(
                'The pdo_mysql Lib is not available.'
            );
        }

        $this->object = pdoInstance(['mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
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

    public function testSettings()
    {
        $this->assertTrue($this->object->settings() instanceof \ezsql\ConfigInterface);
    }

    /**
     * Here starts the MySQL PDO unit test
     */
    public function testMySQLConnect()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertTrue($this->object->connect(null));
    } // testMySQLConnect

    public function testMySQLQuick_connect()
    {
        $this->assertTrue($this->object->quick_connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    }

    public function testMySQLEscape()
    {
        $this->object->quick_connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is\'nt escaped.", $result);
    } // testMySQLEscape

    public function testMySQLSysDate()
    {
        $this->assertEquals("datetime('now')", $this->object->sysDate());
    }

    public function testMySQLCatch_error()
    {
        $this->assertTrue($this->object->connect());

        $this->assertNull($this->object->catch_error());
    }

    public function testMySQLQuery()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertEquals(0, $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'));

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    public function testSecureSetup()
    {
        $this->object->secureSetup();
        $this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD);

        $this->assertEquals(0, $this->object->drop('new_create_test2'));
        $this->assertEquals(
            0,
            $this->object->create(
                'new_create_test2',
                column('id', INTR, 11, notNULL, AUTO),
                column('create_key', VARCHAR, 50),
                primary('id_pk', 'id')
            )
        );

        $this->assertEquals(
            1,
            $this->object->insert(
                'new_create_test2',
                ['create_key' => 'test 2']
            )
        );

        $conn = $this->object->handle();
        $res = $conn->query("SHOW STATUS LIKE 'Ssl_cipher';")->fetchAll();
        $this->assertEquals('Ssl_cipher', $res[0]['Variable_name']);
        $this->assertEquals(0, $this->object->drop('new_create_test2'));
        $this->object->secureReset();
    }

    public function testCreate()
    {
        $this->assertTrue($this->object->connect());

        $this->assertEquals(
            $this->object->create(
                'new_create_test',
                column('id', INTR, 11, notNULL, AUTO),
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
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertEquals($this->object->drop('new_create_test'), 0);
    }

    public function testInsert()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');

        $result = $this->object->insert('unit_test', array('id' => '1', 'test_key' => 'test 1'));
        $this->assertNull($this->object->catch_error());
        $this->assertEquals(0, $result);
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    public function testUpdate()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('id' => '1', 'test_key' => 'test 1'));
        $this->object->insert('unit_test', array('id' => '2', 'test_key' => 'test 2'));
        $this->object->insert('unit_test', array('id' => '3', 'test_key' => 'test 3'));

        $unit_test['test_key'] = 'testing';
        $where = ['id', '=', 1];
        $this->assertEquals($this->object->update('unit_test', $unit_test, $where), 1);

        $this->assertEquals(
            1,
            $this->object->update('unit_test', $unit_test, eq('test_key', 'test 3'), eq('id', 3))
        );

        $this->assertEquals(
            0,
            $this->object->update('unit_test', $unit_test, eq('id', 4))
        );

        $this->assertEquals(
            1,
            $this->object->update('unit_test', $unit_test, eq('test_key', 'test 2'), eq('id', '2'))
        );

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    public function testDelete()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');

        $unit_test['id'] = '1';
        $unit_test['test_key'] = 'test 1';
        $this->object->insert('unit_test', $unit_test);

        $unit_test['id'] = '2';
        $unit_test['test_key'] = 'test 2';
        $this->object->insert('unit_test', $unit_test);

        $unit_test['id'] = '3';
        $unit_test['test_key'] = 'test 3';
        $this->object->insert('unit_test', $unit_test);

        $this->assertEquals($this->object->delete('unit_test', array('id', '=', '1')), 1);

        $this->assertEquals($this->object->delete(
            'unit_test',
            array('test_key', '=', $unit_test['test_key'], 'and'),
            array('id', '=', '3')
        ), 1);

        $where = '1';
        $this->assertEquals($this->object->delete('unit_test', array('test_key', '=', $where)), 0);

        $where = ['id', '=', 2];
        $this->assertEquals($this->object->delete('unit_test', $where), 1);
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    public function testSelect()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('id' => '1', 'test_key' => 'testing 1'));
        $this->object->insert('unit_test', array('id' => '2', 'test_key' => 'testing 2'));
        $this->object->insert('unit_test', array('id' => '3', 'test_key' => 'testing 3'));

        $result = $this->object->select('unit_test');
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            ++$i;
        }

        $where = array('test_key', '=', 'testing 2');
        $result = $this->object->select('unit_test', 'id', $where);
        foreach ($result as $row) {
            $this->assertEquals(2, $row->id);
        }

        $result = $this->object->select('unit_test', 'test_key', array('id', '=', '3'));
        foreach ($result as $row) {
            $this->assertEquals('testing 3', $row->test_key);
        }

        $result = $this->object->select('unit_test', array('test_key'), eq('id', 1));
        foreach ($result as $row) {
            $this->assertEquals('testing 1', $row->test_key);
        }
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    public function testWhereGrouping()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
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

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }

    public function testJoins()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('DROP TABLE unit_test');
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
            $this->assertEquals($o, $row->child_id);
            $this->assertEquals('testing child ' . $o, $row->child_test_key);
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            ++$i;
            --$o;
        }

        $result = $this->object->select('unit_test_child', 'child.parent_id', leftJoin('unit_test_child', 'unit_test', 'parent_id', 'id', 'child'));
        $o = 3;
        foreach ($result as $row) {
            $this->assertEquals($o, $row->parent_id);
            --$o;
        }

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test_child'));
    }

    public function testBeginTransactionCommit()
    {
        $this->object->connect();
        $this->object->query('DROP TABLE unit_test');
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');

        $commit = null;
        try {
            $commit = true;
            $this->object->beginTransaction();
            $this->object->insert('unit_test', array('id' => '1', 'test_key' => 'testing 1'));
            $this->object->insert('unit_test', array('id' => '2', 'test_key' => 'testing 2'));
            $this->object->insert('unit_test', array('id' => '3', 'test_key' => 'testing 3'));
            $this->object->commit();
        } catch (\PDOException $ex) {
            $commit = false;
            $this->object->rollback();
            echo ("Error! This rollback message shouldn't have been displayed: ") . $ex->getMessage();
        }

        if ($commit) {
            $result = $this->object->select('unit_test');
            $i = 1;
            foreach ($result as $row) {
                $this->assertEquals($i, $row->id);
                $this->assertEquals('testing ' . $i, $row->test_key);
                ++$i;
            }

            $this->assertEquals(0, $this->object->drop('unit_test'));
        }
    }

    public function testBeginTransactionRollback()
    {
        $this->object->connect();
        $this->object->query('DROP TABLE unit_test');
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');

        $commit = null;
        try {
            $commit = true;
            $this->object->beginTransaction();
            $this->object->insert('unit_test', array('id' => '1', 'test_key' => 'testing 1'));
            $this->object->insert('unit_test', array('id' => '2', 'test_key' => 'testing 2'));
            $this->object->insert('unit_test', array('idx' => 3, 'test_key' => 'testing 3'));
            $this->object->commit();
        } catch (\PDOException $ex) {
            $commit = false;
            $this->object->rollback();
        }

        if ($commit) {
            echo ("Error! This message shouldn't have been displayed.");
            $result = $this->object->select('unit_test');
            $i = 1;
            foreach ($result as $row) {
                $this->assertEquals('should not be seen ' . $i, $row->test_key);
                ++$i;
            }
            $this->object->drop('unit_test');
        } else {
            //echo ("Error! rollback.");
            $result = $this->object->select('unit_test');
            $i = 1;
            foreach ($result as $row) {
                $this->assertEquals('should not be seen ' . $i, $row->test_key);
                ++$i;
            }

            $this->assertEquals(0, $result);
            $this->object->drop('unit_test');
        }
    }

    public function testMySQLDisconnect()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->assertTrue($this->object->isConnected());
        $this->assertNotNull($this->object->handle());
        $this->object->disconnect();
        $this->assertFalse($this->object->isConnected());
        $this->object->reset();
        $this->assertNull($this->object->handle());
    } // testDisconnect

    public function testMySQLConnectWithOptions()
    {
        $options = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD, $options));
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
                column('id', INTR, 11, notNULL, PRIMARY),
                column('prepare_key', VARCHAR, 50)
            )
        );

        $result = $this->object->query_prepared('INSERT INTO prepare_test( id, prepare_key ) VALUES( ?, ? )', [9, 'test 1']);
        $this->assertEquals(1, $result);

        $this->object->query_prepared('SELECT id, prepare_key FROM prepare_test WHERE id = ?', [9]);

        $query = $this->object->queryResult();
        foreach ($query as $row) {
            $this->assertEquals(9, $row->id);
            $this->assertEquals('test 1', $row->prepare_key);
        }

        $this->object->drop('prepare_test');
    } // testQuery_prepared

    public function test__Construct_Error()
    {
        $this->expectExceptionMessageRegExp('/[Missing configuration details]/');
        $this->assertNull(new ez_pdo());
    }

    public function test__construct()
    {
        unset($GLOBALS['ez' . \Pdo]);
        $dsn = 'mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=3306';
        $settings = Config::initialize('pdo', [$dsn, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
        $this->assertNotNull(new ez_pdo($settings));
    }
}
