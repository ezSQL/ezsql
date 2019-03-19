<?php

namespace ezsql\Tests;

use ezsql\Database;
use ezsql\Tests\DBTestCase;

class pdo_mysqlTest extends DBTestCase 
{
    /**
     * @var ezSQL_pdo
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
        $this->object->setPrepare();
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        $this->object = null;
    } // tearDown
 
     
    /**
     * Here starts the MySQL PDO unit test
     */

    /**
     * @covers ezSQL_pdo::connect
     */
    public function testMySQLConnect() {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));   
        
        $this->assertTrue($this->object->connect(null));
    } // testMySQLConnect

    /**
     * @covers ezSQL_pdo::quick_connect
     */
    public function testMySQLQuick_connect() {
        $this->assertTrue($this->object->quick_connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    } // testMySQLQuick_connect

    /**
     * @covers ezSQL_pdo::escape
     */
    public function testMySQLEscape() {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is\'nt escaped.", $result);
    } // testMySQLEscape

    /**
     * @covers ezSQL_pdo::sysdate
     */
    public function testMySQLSysdate() {
        $this->assertEquals("datetime('now')", $this->object->sysdate());
    } // testMySQLSysdate

    /**
     * @covers ezSQL_pdo::catch_error
     */
    public function testMySQLCatch_error() {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertNull($this->object->catch_error());
    } // testMySQLCatch_error

    /**
     * @covers ezSQL_pdo::query
     */
    public function testMySQLQuery() {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertEquals(0, $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'));

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } // testMySQLQuery
 
    /**
     * @covers ezSQL_pdo::securePDO
     */
    public function testSecurePDO()
    {
        securePDO('mysqli');
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
             
        $this->assertEquals($this->object->drop('new_create_test2'), 0);
        $this->assertEquals($this->object->create('new_create_test2',
            column('id', INTR, 11, notNULL, AUTO),
            column('create_key', VARCHAR, 50),
            primary('id_pk', 'id')), 
        0);

        $this->object->setPrepare(false);
        $this->assertEquals($this->object->insert('new_create_test2',
            ['create_key' => 'test 2']),
        1);

        $conn = $this->object->connection();
        $res = $conn->query("SHOW STATUS LIKE 'Ssl_cipher';")->fetchAll();
        $this->assertEquals('Ssl_cipher', $res[0]['Variable_name']);

        $this->object->setPrepare();
        $this->assertEquals($this->object->drop('new_create_test2'), 0);
    }

    /**
     * @covers ezQuery::create
     */
    public function testCreate()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
             
        $this->assertEquals($this->object->create('new_create_test',
            column('id', INTR, 11, notNULL, AUTO),
            column('create_key', VARCHAR, 50),
            primary('id_pk', 'id')), 
        0);

        $this->object->setPrepare(false);
        $this->assertEquals($this->object->insert('new_create_test',
            ['create_key' => 'test 2']),
        1);
        $this->object->setPrepare();
    }

    /**
     * @covers ezQuery::drop
     */
    public function testDrop()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
             
        $this->assertEquals($this->object->drop('new_create_test'), 0);
    }
   
    /**
     * @covers ezSQLcore::insert
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
     * @covers ezSQLcore::update
     */
    public function testUpdate()
    {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('id'=>'1', 'test_key'=>'test 1' ));
        $this->object->insert('unit_test', array('id'=>'2', 'test_key'=>'test 2' ));
        $this->object->insert('unit_test', array('id'=>'3', 'test_key'=>'test 3' ));
        $unit_test['test_key'] = 'testing';
        $where="id  =  1";
        $this->assertEquals($this->object->update('unit_test', $unit_test, $where), 1);
        $this->assertEquals($this->object->update('unit_test', $unit_test, eq('test_key','test 3', _AND), eq('id','3')), 1);
        $this->assertEquals($this->object->update('unit_test', $unit_test, "id = 4"), 0);
        $this->assertEquals($this->object->update('unit_test', $unit_test, "test_key  =  test 2  and", "id  =  2"), 1);
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }
    
    /**
     * @covers ezSQLcore::delete
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
        $where='1';
        $this->assertEquals($this->object->delete('unit_test', array('id','=','1')), 1);
        $this->assertEquals($this->object->delete('unit_test', 
            array('test_key','=',$unit_test['test_key'],'and'),
            array('id','=','3')), 1);
        $this->assertEquals($this->object->delete('unit_test', array('test_key','=',$where)), 0);
        $where="id  =  2";
        $this->assertEquals($this->object->delete('unit_test', $where), 1);
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }  

    /**
     * @covers ezSQLcore::selecting
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
        
        $where=array('test_key','=','testing 2');
        $result = $this->object->selecting('unit_test', 'id', $where);
        foreach ($result as $row) {
            $this->assertEquals(2, $row->id);
        }
        
        $result = $this->object->selecting('unit_test', 'test_key', array( 'id','=','3' ));
        foreach ($result as $row) {
            $this->assertEquals('testing 3', $row->test_key);
        }
        
        $result = $this->object->selecting('unit_test', array ('test_key'), "id  =  1");
        foreach ($result as $row) {
            $this->assertEquals('testing 1', $row->test_key);
        }
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } 
    
    /**
     * @covers ezSQL_pdo::disconnect
     */
    public function testMySQLDisconnect() {
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->object->disconnect();

        $this->assertFalse($this->object->isConnected());
    } // testMySQLDisconnect

    /**
     * @covers ezSQL_pdo::connect
     */
    public function testMySQLConnectWithOptions() {
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );         
        
        $this->assertTrue($this->object->connect('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD, $options));
    } // testMySQLConnectWithOptions

    /**
     * @covers ezSQL_pdo::__construct
     */
    public function test__Construct() {         
        $this->errors = array();
        set_error_handler(array($this, 'errorHandler'));    
        
        $pdo = $this->getMockBuilder(ezSQL_pdo::class)
        ->setMethods(null)
        ->disableOriginalConstructor()
        ->getMock();
        
        //$this->expectOutputRegex('/[constructor:]/');
        $this->assertNull($pdo->__construct('mysql:host=' . self::TEST_DB_HOST . ';dbname=' . self::TEST_DB_NAME . ';port=' . self::TEST_DB_PORT, self::TEST_DB_USER, self::TEST_DB_PASSWORD));  
    } 
     
} // ezSQL_pdoTest