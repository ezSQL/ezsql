<?php

namespace ezsql\Tests;

use ezsql\Configuration;
use ezsql\Database\ezsqlModel;
use ezsql\Tests\DBTestCase;

class ezsqlModelTest extends DBTestCase 
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
    protected function tearDown() 
    {
        $this->object = null;
    } // tearDown

    /**
     * @covers ezsqlModel::get_host_port
     */
    public function testGet_host_port()
    {
        $hostport = $this->object->get_host_port("localhost:8181");
        $this->assertEquals($hostport[0],"localhost");
        $this->assertEquals($hostport[1],"8181");
    }
	
    /**
     * @covers ezsqlModel::register_error
     */
    public function testRegister_error() 
    {
        $err_str = 'Test error string';
        
        $this->object->register_error($err_str);
        
        $this->assertEquals($err_str, $this->object->last_error);
    } // testRegister_error

    /**
     * @covers ezsqlModel::show_errors
     */
    public function testShow_errors() 
    {
        $this->object->hide_errors();
        
        $this->assertFalse($this->object->getShowErrors());
        
        $this->object->show_errors();
        
        $this->assertTrue($this->object->getShowErrors());
    } // testShow_errors

    /**
     * @covers ezsqlModel::hide_errors
     */
    public function testHide_errors() 
    {
        $this->object->hide_errors();
        
        $this->assertFalse($this->object->getShowErrors());
    } // testHide_errors

    /**
     * @covers ezsqlModel::flush
     */
    public function testFlush() 
    {
        $this->object->flush();
        
        $this->assertNull($this->object->last_result);
        $this->assertNull($this->object->col_info);
        $this->assertNull($this->object->last_query);
        $this->assertFalse($this->object->from_disk_cache);
    } // testFlush

    /**
     * @covers ezsqlModel::get_var
     */
    public function testGet_var() 
    {
        $this->object->last_result = array('1');
        $this->assertNull($this->object->get_var());
        //$this->expectExceptionMessage('Call to undefined method ezsqlModel::query()');
        $this->assertNull($this->object->get_var('1'));
    } // testGet_var

    /**
     * @covers ezsqlModel::get_row
     */
    public function testGet_row() 
    {
        $this->assertNull($this->object->get_row());
        $this->assertNull($this->object->get_row(null,ARRAY_A));
        $this->assertNull($this->object->get_row(null,ARRAY_N));
        $this->assertNull($this->object->get_row(null,'BAD'));
       // $this->expectExceptionMessage('Call to undefined method ezsqlModel::query()');
        $this->assertNull($this->object->get_row('1'));
    } // testGet_row

    /**
     * @covers ezsqlModel::get_col
     */
    public function testGet_col() 
    {
        $this->assertEmpty($this->object->get_col());
        $this->object->last_result = array('1');
        $this->assertNotNull($this->object->get_col());
        //$this->expectExceptionMessage('Call to undefined method ezsqlModel::query()');
        $this->assertNotFalse($this->object->get_col('1'));
    } // testGet_col

    /**
     * @covers ezsqlModel::get_results
     */
    public function testGet_results() 
    {
        $this->assertNull($this->object->get_results());
        $this->assertNotNull($this->object->get_results(null, ARRAY_A));
       // $this->expectExceptionMessage('Call to undefined method ezsqlModel::query()');
        $this->assertNull($this->object->get_results('1'));
    } // testGet_results

    /**
     * @covers ezsqlModel::get_col_info
     */
    public function testGet_col_info() 
    {
        $this->assertEmpty($this->object->get_col_info());
        $this->object->col_info = true;
        $this->assertNull($this->object->get_col_info());
        $this->assertNull($this->object->get_col_info('name',1));
    } // testGet_col_info

    /**
     * @covers ezsqlModel::store_cache
     */
    public function testStore_cache() 
    {
        $sql = 'SELECT * FROM ez_test';
        
        $this->object->store_cache($sql, true);
        
        $this->assertNull($this->object->get_cache($sql));
    } // testStore_cache

    /**
     * @covers ezsqlModel::get_cache
     */
    public function testGet_cache() 
    {
        $sql = 'SELECT * FROM ez_test';
        
        $this->object->store_cache($sql, true);
        
        $this->assertNull($this->object->get_cache($sql));
    } // testGet_cache

    /**
     * The test does not echos HTML, it is just a test, that is still running
     * @covers ezsqlModel::vardump
     */
    public function testVardump() 
    {
        $this->object->debug_echo_is_on = false;
        $this->object->last_result = array('Test 1');
        $this->assertNotEmpty($this->object->vardump($this->object->last_result));
        $this->object->debug_echo_is_on = true;
        $this->expectOutputRegex('/[Last Function Call]/');
        $this->object->vardump('');
        
    } // testVardump

    /**
     * The test echos HTML, it is just a test, that is still running
     * @covers ezsqlModel::dumpvar
     */
    public function testDumpvar() 
    {
        $this->object->last_result = array('Test 1', 'Test 2');
        $this->expectOutputRegex('/[Last Function Call]/');
        $this->object->dumpvar('');
    } // testDumpvar

    /**
     * @covers ezsqlModel::debug
     */
    public function testDebug() 
    {
        $this->assertNotEmpty($this->object->debug(false));
        
        // In addition of getting a result, it fills the console
        $this->expectOutputRegex('/[make a donation]/');
        $this->object->debug(true);
        $this->object->last_error = "test last";
        $this->expectOutputRegex('/[test last]/');
        $this->object->debug(true);
        $this->object->from_disk_cache = true;
        $this->expectOutputRegex('/[Results retrieved from disk cache]/');
        $this->object->debug(true);
        $this->object->col_info = array("just another test");        
        $this->object->debug(false);   
        $this->object->col_info = null;     
        $this->object->last_result = array("just another test II");        
        $this->object->debug(false);
    } // testDebug

    /**
     * @covers ezsqlModel::donation
     */
    public function testDonation() 
    {
        $this->assertNotEmpty($this->object->donation());
    } // testDonation

    /**
     * @covers ezsqlModel::timer_get_cur
     */
    public function testTimer_get_cur() 
    {
        list($usec, $sec) = explode(' ',microtime());
        
        $expected = ((float)$usec + (float)$sec);
        
        $this->assertGreaterThanOrEqual($expected, $this->object->timer_get_cur());
    } // testTimer_get_cur

    /**
     * @covers ezsqlModel::timer_start
     */
    public function testTimer_start() 
    {
        $this->object->timer_start('test_timer');
        $this->assertNotNull($this->object->timers['test_timer']);        
    } // testTimer_start

    /**
     * @covers ezsqlModel::timer_elapsed
     */
    public function testTimer_elapsed() 
    {
        $expected = 0;        
        $this->object->timer_start('test_timer');      
		usleep( 5 );        
        $this->assertGreaterThanOrEqual($expected, $this->object->timer_elapsed('test_timer'));
    } // testTimer_elapsed

    /**
     * @covers ezsqlModel::timer_update_global
     */
    public function testTimer_update_global() 
    {
        $this->object->timer_start('test_timer');           
		usleep( 5 );
        $this->object->do_profile = true;
        $this->object->timer_update_global('test_timer');
        $expected = $this->object->total_query_time;     
        $this->assertGreaterThanOrEqual($expected, $this->object->timer_elapsed('test_timer'));    
    }

    /**
     * @covers ezsqlModel::get_set
     */
    public function testGet_set()
    {
        $this->assertNull($this->object->get_set(''));    
 
        //$this->expectExceptionMessage('Call to undefined method ezsqlModel::escape()');
        $this->assertContains('NOW()',$this->object->get_set(
            array('test_unit'=>'NULL',
            'test_unit2'=>'NOW()',
            'test_unit3'=>'true',
            'test_unit4'=>'false')));
        $this->assertContains('',$this->object->get_set(
            array('test_unit'=>'false')));
        $this->assertContains('',$this->object->get_set(
            array('test_unit'=>'true')));
    }

    /**
     * @covers ezsqlModel::count
     */
    public function testCount()
    {
        $this->assertEquals(0,$this->object->count());
        $this->object->count(true,true);
        $this->assertEquals(1,$this->object->count());
        $this->assertEquals(2,$this->object->count(false,true));
    }
   
    /**
     * @covers ezsqlModel::affectedRows
     */
    public function testAffectedRows() 
    {
        $this->assertEquals(0, $this->object->affectedRows());
    } // testAffectedRows   
    
    /**
     * @covers ezsqlModel::isConnected
     */
    public function testIsConnected() 
    {
        $this->assertFalse($this->object->isConnected());
    }  //testisConnected

    /**
     * @covers ezsqlModel::getShowErrors
     */
    public function testGetShowErrors() 
    {
        $this->assertNotEmpty($this->object->getShowErrors());
    } // testgetShowErrors       
    
    /**
     * @covers ezsqlModel::__construct
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
