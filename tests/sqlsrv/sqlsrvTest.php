<?php

namespace ezsql\Tests;

use ezsql\Configuration;
use ezsql\Database\ez_sqlsrv;
use ezsql\Tests\DBTestCase;

class sqlsrvTest extends DBTestCase 
{

    /**
     * @var ezSQL_sqlsrv
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

        $this->object = new ez_sqlsrv(Configuration);
        $this->object->setPrepare();
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() 
    {
        $this->object->query('DROP TABLE IF EXISTS unit_test');
        $this->object = null;
    } // tearDown

    /**
     * @covers ezSQL_sqlsrv::quick_connect
     */
    public function testQuick_connect() 
    {
        $result = $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->assertTrue($result);
    } // testQuick_connect

    /**
     * @covers ezSQL_sqlsrv::connect
     */
    public function testConnect() 
    {
        $result = $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->assertTrue($result);
    } // testConnect

    /**
     * @covers ezSQL_sqlsrv::escape
     */
    public function testEscape() 
    {
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is\\'nt escaped.", $result);
    } // testEscape

    /**
     * @covers ezSQL_sqlsrv::sysdate
     */
    public function testSysdate() 
    {
        $this->assertEquals('GETDATE()', $this->object->sysdate());
    } // testSysdate
    
    /**
     * @covers ezSQLcore::get_var
     */
    public function testGet_var() 
    { 
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);    
        $current_time = $this->object->get_var("SELECT " . $this->object->sysdate() . " AS 'GetDate()'");
        $this->assertNotNull($current_time);
    } // testGet_var

    /**
     * @covers ezSQLcore::get_results
     */
    public function testGet_results() 
    {           
    $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);    
    
	// Get list of tables from current database..
	$my_tables = $this->object->get_results("select name from ".self::TEST_DB_NAME."..sysobjects where xtype = 'U'",ARRAY_N);
    $this->assertNotNull($my_tables);
    
	// Loop through each row of results..
	foreach ( $my_tables as $table )
        {
            // Get results of DESC table..
            $this->assertNotNull($this->object->query("EXEC SP_COLUMNS '".$table[0]."'"));
        }
    } // testGet_results
    
    /**
     * @covers ezSQL_sqlsrv::query
     */
    public function testQuery() 
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);    
        $this->assertEquals($this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'), 0);
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(1, \'test 1\')'), 1);
        
        $this->object->dbh = null;
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(2, \'test 2\')'),1);
        $this->object->disconnect();
        $this->assertFalse($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(3, \'test 3\')'));    
    } // testQuery

    /**
     * @covers ezSQL_sqlsrv::ConvertMySqlTosqlsrv
     */
    public function testConvertMySqlTosqlsrv() 
    {
        $result = $this->object->ConvertMySqlTosqlsrv("SELECT `test` FROM `unit_test`;");
        $this->assertEquals("SELECT test FROM unit_test;", $result);
    } // testConvertMySqlTosqlsrv
    
    /**
     * @covers ezSQLcore::insert
     */
    public function testInsert()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');        
        $this->assertNotFalse($this->object->insert('unit_test', ['id'=>7, 'test_key'=>'testInsert() 1' ]));
    }
       
    /**
     * @covers ezSQLcore::update
     */
    public function testUpdate()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);  
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');  
        $this->assertNotFalse($this->object->insert('unit_test', array('id'=>1, 'test_key'=>'testUpdate() 1' )));

        $this->object->insert('unit_test', array('id'=>2, 'test_key'=>'testUpdate() 2' ));
        $this->object->insert('unit_test', array('id'=>3, 'test_key'=>'testUpdate() 3' ));

        $unit_test['test_key'] = 'testing';
        $where="id  =  1";

        $this->assertEquals($this->object->update('unit_test', $unit_test, $where), 1);
        $this->assertEquals($this->object->update('unit_test', $unit_test, eq('id', 3, _AND), eq('test_key', 'testUpdate() 3')), 1);
        $this->assertEquals($this->object->update('unit_test', $unit_test, "id = 4"), 0);
        $this->assertEquals($this->object->update('unit_test', $unit_test, "test_key  =  testUpdate() 2  and", "id  =  2"), 1);
    }
    
    /**
     * @covers ezSQLcore::delete
     */
    public function testDelete()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);    
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');
        
        $unit_test['id'] = 1;
        $unit_test['test_key'] = 'testDelete() 1';
        $this->object->insert('unit_test', $unit_test );
        
        $unit_test['id'] = 2;
        $unit_test['test_key'] = 'testDelete() 2';
        $this->object->insert('unit_test', $unit_test );
        
        $unit_test['id'] = 3;
        $unit_test['test_key'] = 'testDelete() 3';
        $this->object->insert('unit_test', $unit_test );
        
        $this->assertEquals($this->object->delete('unit_test', ['id','=',1]), 1);
        $this->assertEquals($this->object->delete('unit_test', eq('id', 3, _AND), eq('test_key', 'testDelete() 3') ), 1);
        $where=1;
        $this->assertFalse($this->object->delete('unit_test', array('test_key','=',$where)));
        $where="id  =  2";
        $this->assertEquals($this->object->delete('unit_test', $where), 1);
    }  

    /**
     * @covers ezSQLcore::selecting
     */
    public function testSelecting()
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);   

        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');

        $this->object->insert('unit_test', array('id'=>8, 'test_key'=>'testing 8' ));
        $this->object->insert('unit_test', array('id'=>9, 'test_key'=>'testing 9' ));
        $this->object->insert('unit_test', array('id'=>10, 'test_key'=>'testing 10' ));
        
        $result = $this->object->selecting('unit_test');
        $i = 8;

        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            ++$i;
        }
        
        $where=eq('test_key','testing 10');
        $result = $this->object->selecting('unit_test', 'id', $where);
        foreach ($result as $row) {
            $this->assertEquals(10, $row->id);
        }
        
        $result = $this->object->selecting('unit_test', 'test_key', eq( 'id',9 ));
        foreach ($result as $row) {
            $this->assertEquals('testing 9', $row->test_key);
        }
        
        $result = $this->object->selecting('unit_test', array ('test_key'), "id  =  8");
        foreach ($result as $row) {
            $this->assertEquals('testing 8', $row->test_key);
        }
    } 
	
    /**
     * @covers ezSQL_sqlsrv::disconnect
     */
    public function testDisconnect() 
    {
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);    
        $this->object->disconnect();
        $this->assertFalse($this->object->isConnected());
    } // testDisconnect
      
    /**
     * @covers ezSQL_sqlsrv::__construct
     */
    public function test__Construct() 
    {   
        $this->errors = array();
        set_error_handler(array($this, 'errorHandler'));    
        
        $sqlsrv = $this->getMockBuilder(ezSQL_sqlsrv::class)
        ->setMethods(null)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->assertNull($sqlsrv->__construct());  
    } 
} // ezSQL_sqlsrvTest