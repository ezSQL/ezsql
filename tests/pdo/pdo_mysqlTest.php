<?php

namespace ezsql\Tests\pdo;

use ezsql\Database;
use ezsql\Config;
use ezsql\Database\ez_pdo;
use ezsql\Tests\EZTestCase;

class pdo_mysqlTest extends EZTestCase 
{
    
    /**
     * constant string database port
     */
    const TEST_DB_PORT = '3306';

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
        if (!extension_loaded('pdo_mysql')) {
            $this->markTestSkipped(
              'The pdo_mysql Lib is not available.'
            );
        }

        $this->object = Database::initialize('pdo', ['mysql:host='.self::TEST_DB_HOST.';dbname='. self::TEST_DB_NAME.';port='.self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
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
     * @covers ezsql\Database\ez_pdo::settings
     */
    public function testSettings()
    {
        $this->assertTrue($this->object->settings() instanceof \ezsql\ConfigInterface);    
    } 

    /**
     * Here starts the MySQL PDO unit test
     */

    /**
     * @covers ezsql\Database\ez_pdo::connect
     */
    public function testMySQLConnect() {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));   
        
        $this->assertTrue($this->object->connect(null));
    } // testMySQLConnect

    /**
     * @covers ezsql\Database\ez_pdo::quick_connect
     */
    public function testMySQLQuick_connect() {
        $this->assertTrue($this->object->quick_connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    }

    /**
     * @covers ezsql\Database\ez_pdo::escape
     */
    public function testMySQLEscape() {
        $this->object->quick_connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is\'nt escaped.", $result);
    } // testMySQLEscape

    /**
     * @covers ezsql\Database\ez_pdo::sysDate
     */
    public function testMySQLSysDate() {
        $this->assertEquals("datetime('now')", $this->object->sysDate());
    }

    /**
     * @covers ezsql\Database\ez_pdo::catch_error
     */
    public function testMySQLCatch_error() {
        $this->assertTrue($this->object->connect());

        $this->assertNull($this->object->catch_error());
    }

    /**
     * @covers ezsql\Database\ez_pdo::query
     */
    public function testMySQLQuery() {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertEquals(0, $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'));

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } 

     /**
     * @covers ezsql\ezsqlModel::secureSetup
     * @covers ezsql\ezsqlModel::secureReset
     * @covers ezsql\Database\ez_pdo::connect
     * @covers ezsql\Database\ez_pdo::handle
     * @covers ezsql\ezQuery::createCertificate
     * @covers ezsql\ezQuery::drop
     * @covers ezsql\ezQuery::create
     * @covers \primary
     * @covers \insert
     */
    public function testSecureSetup()
    {
        $this->object->secureSetup();
        $this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD);
             
        $this->assertEquals(0, $this->object->drop('new_create_test2'));
        $this->assertEquals(0, $this->object->create('new_create_test2',
            column('id', INTR, 11, notNULL, AUTO),
            column('create_key', VARCHAR, 50),
            primary('id_pk', 'id'))
        );

        $this->assertEquals(1, insert('new_create_test2',
            ['create_key' => 'test 2'])
        );

        $conn = $this->object->handle();
        $res = $conn->query("SHOW STATUS LIKE 'Ssl_cipher';")->fetchAll();
        $this->assertEquals('Ssl_cipher', $res[0]['Variable_name']);
        $this->assertEquals(0, $this->object->drop('new_create_test2'));
        $this->object->secureReset();
    }

    /**
     * @covers ezsql\ezQuery::create
     * @covers ezsql\Database\ez_pdo::connect
     */
    public function testCreate()
    {
        $this->assertTrue($this->object->connect());
             
        $this->assertEquals($this->object->create('new_create_test',
            column('id', INTR, 11, notNULL, AUTO),
            column('create_key', VARCHAR, 50),
            primary('id_pk', 'id')), 
        0);

        $this->object->prepareOff();
        $this->assertEquals($this->object->insert('new_create_test',
            ['create_key' => 'test 2']),
        1);
        $this->object->prepareOn();
    }

    /**
     * @covers ezsql\ezQuery::drop
     */
    public function testDrop()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
             
        $this->assertEquals($this->object->drop('new_create_test'), 0);
    }
   
    /**
     * @covers ezsql\ezQuery::insert
     */
    public function testInsert()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        
        $result = $this->object->insert('unit_test', array('id'=>'1', 'test_key'=>'test 1' ));
        $this->assertNull($this->object->catch_error());
        $this->assertEquals(0, $result);
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }
       
    /**
     * @covers ezsql\ezQuery::update
     * @covers \update
     */
    public function testUpdate()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('id'=>'1', 'test_key'=>'test 1' ));
        $this->object->insert('unit_test', array('id'=>'2', 'test_key'=>'test 2' ));
        $this->object->insert('unit_test', array('id'=>'3', 'test_key'=>'test 3' ));

        $unit_test['test_key'] = 'testing';
        $where = ['id', '=', 1];
        $this->assertEquals(update('unit_test', $unit_test, $where), 1);

        $this->assertEquals(1, 
            $this->object->update('unit_test', $unit_test, eq('test_key','test 3'), eq('id', 3)));

        $this->assertEquals(0, 
            $this->object->update('unit_test', $unit_test, eq('id', 4)));

        $this->assertEquals(1, 
            $this->object->update('unit_test', $unit_test, eq('test_key', 'test 2'), eq('id','2')));

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }
    
    /**
     * @covers ezsql\ezQuery::delete
     */
    public function testDelete()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');

        $unit_test['id'] = '1';
        $unit_test['test_key'] = 'test 1';
        $this->object->insert('unit_test', $unit_test );

        $unit_test['id'] = '2';
        $unit_test['test_key'] = 'test 2';
        $this->object->insert('unit_test', $unit_test );

        $unit_test['id'] = '3';
        $unit_test['test_key'] = 'test 3';
        $this->object->insert('unit_test', $unit_test );

        $this->assertEquals($this->object->delete('unit_test', array('id', '=', '1')), 1);

        $this->assertEquals($this->object->delete('unit_test', 
            array('test_key','=',$unit_test['test_key'],'and'),
            array('id','=','3')), 1);

        $where = '1';
        $this->assertEquals($this->object->delete('unit_test', array('test_key', '=', $where)), 0);

        $where = ['id', '=', 2];
        $this->assertEquals($this->object->delete('unit_test', $where), 1);
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }  

    /**
     * @covers ezsql\ezQuery::selecting
     * @covers ezsql\Database\ez_pdo::query
     * @covers ezsql\Database\ez_pdo::prepareValues
     * @covers ezsql\Database\ez_pdo::query_prepared
     * @covers \select
     */
    public function testSelecting()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('id'=>'1', 'test_key'=>'testing 1' ));
        $this->object->insert('unit_test', array('id'=>'2', 'test_key'=>'testing 2' ));
        $this->object->insert('unit_test', array('id'=>'3', 'test_key'=>'testing 3' ));
        
        $result = $this->object->selecting('unit_test');
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            ++$i;
        }
        
        $where = array('test_key', '=', 'testing 2');
        $result = select('unit_test', 'id', $where);
        foreach ($result as $row) {
            $this->assertEquals(2, $row->id);
        }
        
        $result = $this->object->selecting('unit_test', 'test_key', array( 'id', '=', '3' ));
        foreach ($result as $row) {
            $this->assertEquals('testing 3', $row->test_key);
        }
        
        $result = $this->object->selecting('unit_test', array ('test_key'), eq('id', 1));
        foreach ($result as $row) {
            $this->assertEquals('testing 1', $row->test_key);
        }
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } 
    
    /**
     * @covers ezsql\Database\ez_pdo::disconnect
     * @covers ezsql\Database\ez_pdo::reset
     * @covers ezsql\Database\ez_pdo::handle
     */
    public function testMySQLDisconnect() {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->assertTrue($this->object->isConnected());
        $this->assertNotNull($this->object->handle());
        $this->object->disconnect();
        $this->assertFalse($this->object->isConnected());
        $this->object->reset();
        $this->assertNull($this->object->handle());
    } // testDisconnect

    /**
     * @covers ezsql\Database\ez_pdo::connect
     */
    public function testMySQLConnectWithOptions() {
        $options = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );         
        
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD, $options));
    }

    /**
     * @covers ezsql\Database\ez_pdo::__construct
     */
    public function test__Construct_Error() 
    {        
        $this->expectExceptionMessageRegExp('/[Missing configuration details]/');
        $this->assertNull(new ez_pdo());
    }

    /**
     * @covers ezsql\Database\ez_pdo::__construct
     */
    public function test__construct() 
    {
        unset($GLOBALS['ez'.\Pdo]);
        $dsn = 'mysql:host='.self::TEST_DB_HOST.';dbname='. self::TEST_DB_NAME.';port=3306';
        $settings = Config::initialize('pdo', [$dsn, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
        $this->assertNotNull(new ez_pdo($settings));
    } 
}
