<?php

namespace ezsql\Tests;

use ezsql\Database;
use ezsql\Tests\DBTestCase;

class postgresqlTest extends DBTestCase 
{
    /**
     * constant database port 
     */
    const TEST_DB_PORT = '5432';
    
    /**
     * @var ezSQL_postgresql
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
     * @covers ezSQL_postgresql::quick_connect
     */
    public function testQuick_connect() {
        $this->assertTrue($this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
    } // testQuick_connect

    /**
     * @covers ezSQL_postgresql::connect
     * 
     */
    public function testConnect() {        
        $this->errors = array();
        set_error_handler(array($this, 'errorHandler')); 
         
        $this->assertFalse($this->object->connect('',''));  
        $this->assertFalse($this->object->connect('self::TEST_DB_USER', 'self::TEST_DB_PASSWORD',' self::TEST_DB_NAME', 'self::TEST_DB_CHARSET'));  
        
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
    } // testConnect

    /**
     * @covers ezSQL_postgresql::escape
     */
    public function testEscape() {
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testEscape

    /**
     * @covers ezSQL_postgresql::sysdate
     */
    public function testSysdate() {
        $this->assertEquals('NOW()', $this->object->sysdate());
    } // testSysdate

    /**
     * @covers ezSQL_postgresql::showTables
     */
    public function testShowTables() {
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
        
        $result = $this->object->showTables();
        
        $this->assertEquals('SELECT table_name FROM information_schema.tables WHERE table_schema = \'' . self::TEST_DB_NAME . '\' AND table_type=\'BASE TABLE\'', $result);
    } // testShowTables

    /**
     * @covers ezSQL_postgresql::descTable
     */
    public function testDescTable() {
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
        
        $this->assertEquals(0, $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'));
        
        $this->assertEquals(
                "SELECT ordinal_position, column_name, data_type, column_default, is_nullable, character_maximum_length, numeric_precision FROM information_schema.columns WHERE table_name = 'unit_test' AND table_schema='" . self::TEST_DB_NAME . "' ORDER BY ordinal_position",
                $this->object->descTable('unit_test')
        );
        
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } // testDescTable

    /**
     * @covers ezSQL_postgresql::showDatabases
     */
    public function testShowDatabases() {
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
        
        $this->assertEquals(
                "SELECT datname FROM pg_database WHERE datname NOT IN ('template0', 'template1') ORDER BY 1",
                $this->object->showDatabases()
        );
    } // testShowDatabases

    /**
     * @covers ezSQL_postgresql::query
     */
    public function testQuery() {
        $this->assertTrue($this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT));
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
            
        $this->object->query('CREATE TABLE unit_test(id serial, test_key varchar(50), test_value varchar(50), PRIMARY KEY (ID))');
        $this->assertEquals($this->object->query('INSERT INTO unit_test(test_key, test_value) VALUES(\'test 1\', \'testing string 1\')'), 1);
        
        $this->object->dbh = null;
        $this->assertNull($this->object->query('INSERT INTO unit_test(test_key, test_value) VALUES(\'test 2\', \'testing string 2\')'));
        $this->object->disconnect();
        $this->assertNull($this->object->query('INSERT INTO unit_test(test_key, test_value) VALUES(\'test 3\', \'testing string 3\')'));    
        
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } // testQuery

     /**
     * @covers ezQuery::create
     */
    public function testCreate()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
             
        $this->assertEquals($this->object->create('new_create_test',
            column('id', AUTO),
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
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
             
        $this->assertEquals($this->object->drop('new_create_test'), 0);
    }
    
    /**
     * @covers ezSQLcore::insert
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
     * @covers ezSQLcore::update
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
     * @covers ezSQLcore::delete
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
     * @covers ezSQL_postgresql::disconnect
     */
    public function testDisconnect() {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);  
        $this->object->disconnect();
        
        $this->assertFalse($this->object->isConnected());
    } // testDisconnect

    /**
     * @covers ezSQL_postgresql::getDBHost
     */
    public function testGetDBHost() {
        $this->assertEquals(self::TEST_DB_HOST, $this->object->getDBHost());
    } // testGetDBHost

    /**
     * @covers ezSQL_postgresql::getPort
     */
    public function testGetPort() {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_HOST, self::TEST_DB_PORT);
        
        $this->assertEquals(self::TEST_DB_PORT, $this->object->getPort());
    } // testGetPort

    /**
     * @covers ezSQLcore::selecting
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
     * @covers ezSQL_postgresql::__construct
     */
    public function test__Construct() {     
        $postgresql = $this->getMockBuilder(ezSQL_postgresql::class)
        ->setMethods(null)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->assertNull($postgresql->__construct());  
        $this->assertNull($postgresql->__construct('testuser','','','','utf8'));  
    } 

} // ezSQL_postgresqlTest