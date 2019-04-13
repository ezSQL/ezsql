<?php

namespace ezsql\Tests\postgresql;

use ezsql\Database;
use ezsql\Config;
use ezsql\Database\ez_pgsql;
use ezsql\Tests\EZTestCase;

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
        
        $this->object = Database::initialize('pgsql', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT]); 
        $this->object->prepareOn();
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown():void
    {
        $this->object = null;
    } // tearDown
 
    /**
     * @covers ezsql\Database\ez_pgsql::settings
     */
    public function testSettings()
    {
        $this->assertTrue($this->object->settings() instanceof \ezsql\ConfigInterface);    
    } 

    /**
     * @covers ezsql\Database\ez_pgsql::quick_connect
     */
    public function testQuick_connect() {
        $this->assertTrue($this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
    } // testQuick_connect

    /**
     * @covers ezsql\Database\ez_pgsql::connect
     * 
     */
    public function testConnect() {        
        $this->errors = array();
        set_error_handler(array($this, 'errorHandler')); 
         
        $this->assertFalse($this->object->connect('self::TEST_DB_USER', 'self::TEST_DB_PASSWORD','self::TEST_DB_NAME', 'self::TEST_DB_CHARSET'));  
        
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
        
        $this->assertTrue($this->object->connect());
    } // testConnect

    /**
     * @covers ezsql\Database\ez_pgsql::escape
     */
    public function testEscape() {
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testEscape

    /**
     * @covers ezsql\Database\ez_pgsql::sysDate
     */
    public function testSysdate() {
        $this->assertEquals('NOW()', $this->object->sysDate());
    }

    /**
     * @covers ezsql\Database\ez_pgsql::query
     */
    public function testQuery() {
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
            
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $this->assertEquals($this->object->query('INSERT INTO unit_test(test_key, test_value) VALUES(\'test 1\', \'testing string 1\')'), 1);
        
        $this->object->reset();
        $this->assertNotNull($this->object->query('INSERT INTO unit_test(test_key, test_value) VALUES(\'test 2\', \'testing string 2\')'));
        $this->object->disconnect();
        $this->assertFalse($this->object->query('INSERT INTO unit_test(test_key, test_value) VALUES(\'test 3\', \'testing string 3\')'));    
        
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } // testQuery

     /**
     * @covers ezsql\ezQuery::create
     */
    public function testCreate()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
             
        $this->assertEquals($this->object->create('new_create_test',
            column('id', AUTO),
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
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
             
        $this->assertEquals($this->object->drop('new_create_test'), 0);
    }
    
    /**
     * @covers ezsql\ezQuery::insert
     */
    public function testInsert()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);     
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));   
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $result = $this->object->insert('unit_test', array('test_key'=>'test 1', 'test_value'=>'testing string 1' ));
        $this->assertEquals($result, 1);
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }
       
    /**
     * @covers ezsql\ezQuery::update
     */
    public function testUpdate()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);   
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('test_key'=>'test 1', 'test_value'=>'testing string 1' ));
        $this->object->insert('unit_test', array('test_key'=>'test 2', 'test_value'=>'testing string 2' ));
        $result = $this->object->insert('unit_test', array('test_key'=>'test 3', 'test_value'=>'testing string 3' ));
        $this->assertEquals($result, 3);
        $unit_test['test_key'] = 'the key string';
        $where="test_key  =  test 1";
        $this->assertEquals(1, $this->object->update('unit_test', $unit_test, $where));
        $this->assertEquals(1, $this->object->update('unit_test', $unit_test, 
			array('test_key',EQ,'test 3','and'),
			array('test_value','=','testing string 3')));
        $where=array('test_value',EQ,'testing string 4');
        $this->assertEquals(0, $this->object->update('unit_test', $unit_test, $where));
        $this->assertEquals(1, $this->object->update('unit_test', $unit_test, "test_key  =  test 2"));
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }
    
    /**
     * @covers ezsql\ezQuery::delete
     */
    public function testDelete()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('test_key'=>'test 1', 'test_value'=>'testing string 1' ));
        $this->object->insert('unit_test', array('test_key'=>'test 2', 'test_value'=>'testing string 2' ));
        $this->object->insert('unit_test', array('test_key'=>'test 3', 'test_value'=>'testing string 3' ));   

        $where=array('test_key','=','test 1');
        $this->assertEquals($this->object->delete('unit_test', $where), 1);
        
        $this->assertEquals($this->object->delete('unit_test', 
            array('test_key','=','test 3'),
            array('test_value','=','testing string 3')), 1);
        $where=array('test_value','=','testing 2');
        $this->assertEquals(0, $this->object->delete('unit_test', $where));
        $where="test_key  =  test 2";
        $this->assertEquals(1, $this->object->delete('unit_test', $where));
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }  
	
    /**
     * @covers ezsql\Database\ez_pgsql::disconnect
     * @covers ezsql\Database\ez_pgsql::reset
     * @covers ezsql\Database\ez_pgsql::handle
     */
    public function testDisconnect() {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT); 
        $this->assertTrue($this->object->isConnected());
        $this->assertNotNull($this->object->handle());
        $this->object->disconnect();
        $this->assertFalse($this->object->isConnected());
        $this->object->reset();
        $this->assertNull($this->object->handle());
    } // testDisconnect

    /**
     * @covers ezsql\Database\ez_pgsql::getHost
     */
    public function testGetHost() {
        $this->assertEquals(self::TEST_DB_HOST, $this->object->getHost());
    } // testGetDBHost

    /**
     * @covers ezsql\Database\ez_pgsql::getPort
     */
    public function testGetPort() {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
        
        $this->assertEquals(self::TEST_DB_PORT, $this->object->getPort());
    } // testGetPort

    /**
     * @covers ezsql\ezQuery::selecting
     * @covers ezsql\Database\ez_pgsql::query
     * @covers ezsql\Database\ez_pgsql::prepareValues
     * @covers ezsql\Database\ez_pgsql::query_prepared
     */
    public function testSelecting()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $this->object->insert('unit_test', array('test_key'=>'test 1', 'test_value'=>'testing string 1' ));
        $this->object->insert('unit_test', array('test_key'=>'test 2', 'test_value'=>'testing string 2' ));
        $this->object->insert('unit_test', array('test_key'=>'test 3', 'test_value'=>'testing string 3' ));   
        
        $result = $this->object->selecting('unit_test');        
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing string ' . $i, $row->test_value);
            $this->assertEquals('test ' . $i, $row->test_key);
            ++$i;
        }
        
        $where = eq('id','2');
        $result = $this->object->selecting('unit_test', 'id', $this->object->where($where));
        foreach ($result as $row) {
            $this->assertEquals(2, $row->id);
        }
        
        $where = [eq('test_value','testing string 3', _AND), eq('id','3')];
        $result = $this->object->selecting('unit_test', 'test_key', $this->object->where($where));
        foreach ($result as $row) {
            $this->assertEquals('test 3', $row->test_key);
        }      
        
        $result = $this->object->selecting('unit_test', 'test_value', $this->object->where(eq( 'test_key','test 1' )));
        foreach ($result as $row) {
            $this->assertEquals('testing string 1', $row->test_value);
        }
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } 
    
    /**
     * @covers ezsql\Database\ez_pgsql::__construct
     */
    public function test__Construct_Error() 
    { 
        $this->expectExceptionMessageRegExp('/[Missing configuration details]/');
        $this->assertNull(new ez_pgsql());
    } 
       
    /**
     * @covers ezsql\Database\ez_pgsql::__construct
     */
    public function test__construct() 
    {
        unset($GLOBALS['ez'.\PGSQL]);
        $settings = new Config('pgsql', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT]);
        $this->assertNotNull(new ez_pgsql($settings));
    } 
} // ezsql\Database\ez_pgsqlTest