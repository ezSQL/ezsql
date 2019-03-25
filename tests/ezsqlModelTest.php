<?php

namespace ezsql\Tests;

use ezsql\ezsqlModel;
use ezsql\Tests\EZTestCase;

class ezsqlModelTest extends EZTestCase 
{	
    /**
     * @var ezsqlModel
     */
    protected $object;
	
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
	{
        $this->object = new ezsqlModel();
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
     * @covers ezsql\ezsqlModel::get_host_port
     */
    public function testGet_host_port()
    {
        $hostPort = $this->object->get_host_port("localhost:8181");
        $this->assertEquals($hostPort[0],"localhost");
        $this->assertEquals($hostPort[1],"8181");
    }
    
    /**
     * @covers ezsql\ezsqlModel::__call
     */
    public function testGetCache_Timeout()
    {
        $res = $this->object->getCache_Timeout();
        $this->assertEquals(24, $res);
    }

    /**
     * @covers ezsql\ezsqlModel::__call
     */
    public function testSetCache_Timeout()
    {
        $this->object->setCache_Timeout(44);
        $this->assertEquals(44, $this->object->getCache_Timeout());
    }

    /**
     * @covers ezsql\ezsqlModel::__call
     */
    public function testGetNotProperty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/does not exist/');
        $res = $this->object->getNotProperty();
    }
		
    /**
     * @covers ezsql\ezsqlModel::register_error
     */
    public function testRegister_error() 
    {
        $err_str = 'Test error string';
        
        $this->object->register_error($err_str);
        
        $this->assertEquals($err_str, $this->object->getLast_Error());
    } // testRegister_error

    /**
     * @covers ezsql\ezsqlModel::show_errors
     */
    public function testShow_errors() 
    {
        $this->object->hide_errors();
        
        $this->assertFalse($this->object->getShow_Errors());
        
        $this->object->show_errors();
        
        $this->assertTrue($this->object->getShow_Errors());
    } // testShow_errors

    /**
     * @covers ezsql\ezsqlModel::hide_errors
     */
    public function testHide_errors() 
    {
        $this->object->hide_errors();
        
        $this->assertFalse($this->object->getShow_Errors());
    } // testHide_errors

    /**
     * @covers ezsql\ezsqlModel::flush
     */
    public function testFlush() 
    {
        $this->object->flush();
        
        $this->assertNull($this->object->getLast_Result());
        $this->assertNull($this->object->getLast_Query());
        $this->assertNull($this->object->getCol_Info());
        $this->assertFalse($this->object->getFrom_Disk_Cache());
    } // testFlush

    /**
     * @covers ezsql\ezsqlModel::get_var
     */
    public function testGet_var() 
    {
        $this->assertEmpty($this->object->get_var());
        $this->object->setLast_Result([new \stdClass]);
        $this->assertNull($this->object->get_var());
        $this->assertNull($this->object->get_var('1'));
    } // testGet_var

    /**
     * @covers ezsql\ezsqlModel::get_row
     */
    public function testGet_row() 
    {
        $this->assertNull($this->object->get_row());
        $this->assertNull($this->object->get_row(null, ARRAY_A));
        $this->assertNull($this->object->get_row(null, ARRAY_N));
        $this->object->hide_errors();
        $this->assertNull($this->object->get_row(null, 'BAD'));
        $this->assertNull($this->object->get_row('1'));
    } // testGet_row

    /**
     * @covers ezsql\ezsqlModel::get_col
     */
    public function testGet_col() 
    {
        $this->assertEmpty($this->object->get_col());
        $this->object->setLast_Result([new \stdClass]);
        $this->assertNotNull($this->object->get_col());
        $this->assertNotFalse($this->object->get_col('1'));
    } // testGet_col

    /**
     * @covers ezsql\ezsqlModel::get_results
     */
    public function testGet_results() 
    {
        $this->assertNull($this->object->get_results());
        $this->assertNotNull($this->object->get_results(null, ARRAY_A));
        $this->assertNull($this->object->get_results('1'));
    } // testGet_results

    /**
     * @covers ezsql\ezsqlModel::get_col_info
     */
    public function testGet_col_info() 
    {
        $this->assertEmpty($this->object->get_col_info());
        $this->object->setCol_Info([]);
        $this->assertNull($this->object->get_col_info());
        $this->assertNull($this->object->get_col_info('name', 1));
    } // testGet_col_info

    /**
     * @covers ezsql\ezsqlModel::store_cache
     */
    public function testStore_cache() 
    {
        $sql = 'SELECT * FROM ez_test';
        
        $this->object->store_cache($sql, true);
        
        $this->assertNull($this->object->get_cache($sql));
    } // testStore_cache

    /**
     * @covers ezsql\ezsqlModel::get_cache
     */
    public function testGet_cache() 
    {
        $sql = 'SELECT * FROM ez_test';
        
        $this->object->store_cache($sql, true);
        
        $this->assertNull($this->object->get_cache($sql));
    } // testGet_cache

    /**
     * The test does not echos HTML, it is just a test, that is still running
     * @covers ezsql\ezsqlModel::varDump
     */
    public function testVarDump() 
    {
        $this->object->setDebug_Echo_Is_On(false);
        $this->object->setLast_Result(['test 1']);
        $this->assertNotEmpty($this->object->vardump($this->object->getLast_Result()));
        $this->object->setDebug_Echo_Is_On(true);
        $this->expectOutputRegex('/[Last Function Call]/');
        $this->object->varDump('');        
    } // testVardump

    /**
     * The test echos HTML, it is just a test, that is still running
     * @covers ezsql\ezsqlModel::dump_var
     */
    public function testDump_var() 
    {
        $this->object->setLast_Result(['Test 1', 'Test 2']);
        $this->expectOutputRegex('/[Last Function Call]/');
        $this->object->dump_var();
    } // testDump_var

    /**
     * @covers ezsql\ezsqlModel::debug
     */
    public function testDebug() 
    {
        $this->assertNotEmpty($this->object->debug(false));
        
        // In addition of getting a result, it fills the console
        $this->expectOutputRegex('/[make a donation]/');
        $this->object->debug(true);
        $this->object->setLast_Error("test last");
        $this->expectOutputRegex('/[test last]/');
        $this->object->debug(true);
        $this->object->setFrom_Disk_Cache(true);
        $this->expectOutputRegex('/[Results retrieved from disk cache]/');
        $this->object->debug(true);
        $this->object->setCol_Info(["just another test"]);
        $this->object->debug(false);   
        $this->object->setCol_Info(null);
        $this->object->setLast_Result(["just another test II"]);        
        $this->object->debug(false);
    } // testDebug

    /**
     * @covers ezsql\ezsqlModel::timer_get_cur
     */
    public function testTimer_get_cur() 
    {
        list($usec, $sec) = explode(' ',microtime());
        
        $expected = ((float)$usec + (float)$sec);
        
        $this->assertGreaterThanOrEqual($expected, $this->object->timer_get_cur());
    } // testTimer_get_cur

    /**
     * @covers ezsql\ezsqlModel::timer_start
     */
    public function testTimer_start() 
    {
        $this->object->timer_start('test_timer');
        $this->assertNotNull($this->object->getTimers());        
    } // testTimer_start

    /**
     * @covers ezsql\ezsqlModel::timer_elapsed
     */
    public function testTimer_elapsed() 
    {
        $expected = 0;        
        $this->object->timer_start('test_timer');      
		usleep( 5 );        
        $this->assertGreaterThanOrEqual($expected, $this->object->timer_elapsed('test_timer'));
    } // testTimer_elapsed

    /**
     * @covers ezsql\ezsqlModel::timer_update_global
     */
    public function testTimer_update_global() 
    {
        $this->object->timer_start('test_timer');           
		usleep( 5 );
        $this->object->setDo_Profile(true);
        $this->object->timer_update_global('test_timer');
        $expected = $this->object->getTotal_Query_Time();     
        $this->assertGreaterThanOrEqual($expected, $this->object->timer_elapsed('test_timer'));    
    }

    /**
     * @covers ezsql\ezsqlModel::count
     */
    public function testCount()
    {
        $this->assertEquals(0,$this->object->count());
        $this->object->count(true,true);
        $this->assertEquals(1,$this->object->count());
        $this->assertEquals(2,$this->object->count(false,true));
    }
   
    /**
     * @covers ezsql\ezsqlModel::affectedRows
     */
    public function testAffectedRows() 
    {
        $this->assertEquals(0, $this->object->affectedRows());
    } // testAffectedRows   
    
    /**
     * @covers ezsql\ezsqlModel::isConnected
     */
    public function testIsConnected() 
    {
        $this->assertFalse($this->object->isConnected());
    }  //testisConnected

    /**
     * @covers ezsql\ezsqlModel::__construct
     */
    public function test__Construct() 
    {         
        $ezsqlModel = $this->getMockBuilder(ezsqlModel::class)
        ->setMethods(null)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->assertNull($ezsqlModel->__construct());  
    }
} //
