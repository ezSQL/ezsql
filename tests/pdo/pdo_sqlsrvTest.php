<?php

namespace ezsql\Tests;

use ezsql\Database;
use ezsql\Database\ez_pdo;
use ezsql\Tests\EZTestCase;

class pdo_sqlsrvTest extends EZTestCase 
{    
    /**
     * @var ezsql\Database\ez_pdo
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
        
        $this->object = Database::initialize('pdo', ['sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD]);
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
     * @covers ezsql\Database\ez_pdo::connect
     */
    public function testSQLsrvConnect() {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    } // testSQLsrvConnect

    /**
     * @covers ezsql\Database\ez_pdo::quick_connect
     */
    public function testSQLsrvQuick_connect() {
        $this->assertTrue($this->object->quick_connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
    } // testSQLsrvQuick_connect

     /**
     * @covers ezsql\Database\ez_pdo::escape
     */
    public function testSQLsrvEscape() {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testSQLsrvEscape

    /**
     * @covers ezsql\Database\ez_pdo::sysdate
     */
    public function testSQLsrvSysdate() {
        $this->assertEquals("datetime('now')", $this->object->sysdate());
    } // testSQLsrvSysdate

    /**
     * @covers ezsql\Database\ez_pdo::catch_error
     */
    public function testSQLsrvCatch_error() {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->object->query('DROP TABLE unit_test2');
        $this->assertTrue($this->object->catch_error());
    } // testSQLsrvCatch_error

    /**
     * @covers ezsql\Database\ez_pdo::query
     */
    public function testSQLsrvQuery() {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->assertEquals(0, $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'));

        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    } // testSQLsrvQuery
    
    /**
     * @covers ezsql\ezQuery::insert
     */
    public function testInsert()
    {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));
        $this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))');      
        $this->assertNotFalse($this->object->insert('unit_test', ['id'=>7, 'test_key'=>'testInsert() 1' ]));
        $this->assertEquals(0, $this->object->query('DROP TABLE unit_test'));
    }
       
    /**
     * @covers ezsql\ezQuery::update
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
     * @covers ezsql\ezQuery::delete
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
     * @covers ezsql\ezQuery::selecting
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
     * @covers ezsql\Database\ez_pdo::disconnect
     */
    public function testSQLsrvDisconnect() {
        $this->assertTrue($this->object->connect('sqlsrv:Server=' . self::TEST_DB_HOST . ';Database=' . self::TEST_DB_NAME, self::TEST_DB_USER, self::TEST_DB_PASSWORD));

        $this->object->disconnect();

        $this->assertFalse($this->object->isConnected());
    } // testSQLsrvDisconnect
    
    /**
     * @covers ezsql\Database\ez_pdo::__construct
     */
    public function test__Construct() {
        $this->expectExceptionMessageRegExp('/[Missing configuration details]/');
        $this->assertNull(new ez_pdo);
    }     
} // ezsql\Database\ez_pdoTest
