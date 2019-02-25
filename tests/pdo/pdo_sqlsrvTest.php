<?php

namespace ezsql\Tests;

use ezsql\Configuration;
use ezsql\Database\ez_pdo;
use ezsql\Tests\DBTestCase;

class pdo_sqlsrvTest extends DBTestCase 
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
        if (!extension_loaded('pdo_sqlsrv')) {
            $this->markTestSkipped(
              'The pdo_sqlsrv Lib is not available.'
            );
        }
        $this->object = new ez_pdo(Configuration);
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
     * @covers ezSQL_pdo::connect
     */
    public function testSQLsrvConnect() {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    } // testSQLsrvConnect

    /**
     * @covers ezSQL_pdo::quick_connect
     */
    public function testSQLsrvQuick_connect() {
        $this->assertTrue($this->object->quick_connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    } // testSQLsrvQuick_connect

     /**
     * @covers ezSQL_pdo::escape
     */
    public function testSQLsrvEscape() {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testSQLsrvEscape

    /**
     * @covers ezSQL_pdo::sysdate
     */
    public function testSQLsrvSysdate() {
        $this->assertEquals("datetime('now')", $this->object->sysdate());
    } // testSQLsrvSysdate

    /**
     * @covers ezSQL_pdo::catch_error
     */
    public function testSQLsrvCatch_error() {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->object->query('DROP TABLE unit_test2');
        $this->assertTrue($this->object->catch_error());
    } // testSQLsrvCatch_error

    /**
     * @covers ezSQL_pdo::query
     */
    public function testSQLsrvQuery() {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertEquals(0, $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'));

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } // testSQLsrvQuery
    
    /**
     * @covers ezSQLcore::insert
     */
    public function testInsert()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');      
        $this->assertNotFalse($this->object->insert('unit_test', ['id'=>7, 'test_key'=>'testInsert() 1' ]));
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }
       
    /**
     * @covers ezSQLcore::update
     */
    public function testUpdate()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));  
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
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD)); 

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
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
        $this->assertEquals(0,$this->object->delete('unit_test', array('test_key','=',$where)));
        $where="id  =  2";
        $this->assertEquals($this->object->delete('unit_test', $where), 1);
    }  

    /**
     * @covers ezSQLcore::selecting
     */
    public function testSelecting()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
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
     * @covers ezSQL_pdo::disconnect
     */
    public function testSQLsrvDisconnect() {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->object->disconnect();

        $this->assertFalse($this->object->isConnected());
    } // testSQLsrvDisconnect

    /**
     * @covers ezSQLcore::get_set
     */
    public function testGet_set() {
        $expected = "test_var1 = '1', test_var2 = 'ezSQL test', test_var3 = 'This is''nt escaped.'";
        
        $params = array(
            'test_var1' => 1,
            'test_var2' => 'ezSQL test',
            'test_var3' => "This is'nt escaped."
        );
        
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertequals($expected, $this->object->get_set($params)); 
        $this->assertContains('NOW()',$this->object->get_set(array('test_var1' => 1,'test_var2'=>'NOW()')));
        $this->assertContains("test_var2 = 0", $this->object->get_set(array('test_var2'=>'false')));
        $this->assertContains("test_var2 = '1'", $this->object->get_set(array('test_var2'=>'true')));
    } // testSQLiteGet_set
    
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
        
        $this->assertNull($pdo->__construct('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD)); 
    }     
} // ezSQL_pdoTest