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
    }

    public function testGet_host_port()
    {
        $hostPort = $this->object->get_host_port("localhost:8181");
        $this->assertEquals($hostPort[0], "localhost");
        $this->assertEquals($hostPort[1], "8181");
    }

    public function testGetCache_Timeout()
    {
        $res = $this->object->getCacheTimeout();
        $this->assertEquals(24, $res);
    }

    public function testSetCache_Timeout()
    {
        $this->object->setCache_Timeout(44);
        $this->assertEquals(44, $this->object->getCache_Timeout());
    }

    public function testGetNotProperty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/does not exist/');
        $res = $this->object->getNotProperty();
    }

    public function testRegister_error()
    {
        $err_str = 'Test error string';

        $this->object->hide_errors();
        $this->object->register_error($err_str);

        $this->assertEquals($err_str, $this->object->getLast_Error());

        $this->object->show_errors();
        set_error_handler([$this, 'errorHandler']);
        $this->assertFalse($this->object->register_error($err_str));
        $this->object->hide_errors();
    }

    public function testShow_errors()
    {
        $this->object->hide_errors();

        $this->assertFalse($this->object->getShow_Errors());

        $this->object->show_errors();

        $this->assertTrue($this->object->getShow_Errors());
    }

    public function testHide_errors()
    {
        $this->object->hide_errors();

        $this->assertFalse($this->object->getShow_Errors());
    }

    public function testFlush()
    {
        $this->object->flush();

        $this->assertNull($this->object->getLast_Result());
        $this->assertNull($this->object->getLast_Query());
        $this->assertEquals([], $this->object->getCol_Info());
        $this->assertFalse($this->object->getFrom_Disk_Cache());
    }

    public function testGet_var()
    {
        $this->assertEmpty($this->object->get_var());
        $this->object->setLast_Result([new \stdClass]);
        $this->assertNull($this->object->get_var());
        $this->assertNull($this->object->get_var('1'));
    }

    public function testGet_row()
    {
        $this->assertNull($this->object->get_row());
        $this->assertNull($this->object->get_row(null, ARRAY_A));
        $this->assertNull($this->object->get_row(null, ARRAY_N));
        $this->object->hide_errors();
        $this->assertNull($this->object->get_row(null, 'BAD'));
        $this->assertNull($this->object->get_row('1'));
    }

    public function testGet_col()
    {
        $this->assertEmpty($this->object->get_col());
        $this->object->setLast_Result([new \stdClass]);
        $this->assertNotNull($this->object->get_col());
        $this->assertNotFalse($this->object->get_col('1'));
    }

    public function testGet_results()
    {
        $this->assertNull($this->object->get_results());
        $this->assertNotNull($this->object->get_results(null, ARRAY_A));
        $this->assertNull($this->object->get_results('1'));
    }

    public function testGet_col_info()
    {
        $this->assertEmpty($this->object->get_col_info());
        $this->object->setColInfo([]);
        $this->assertNull($this->object->get_col_info());
        $this->assertNull($this->object->get_col_info('name', 1));
    }

    public function testStore_cache()
    {
        $sql = 'SELECT * FROM ez_test';
        $this->object->setCacheTimeout(1);
        $this->object->setUseDiskCache(true);
        $this->object->setCacheQueries(true);
        $this->object->setNumRows(5);
        $this->object->store_cache($sql, false);

        $this->assertEquals(5, $this->object->get_cache($sql));
    }

    public function testGet_cache()
    {
        $sql = 'SELECT * FROM ez_test';
        $this->object->setCache_Timeout(1);
        $this->object->setUse_Disk_Cache(true);
        $this->object->setCache_Queries(true);
        $this->object->setNum_Rows(2);
        $this->object->store_cache($sql, false);

        $this->assertEquals(2, $this->object->get_cache($sql));
    }

    /**
     * The test does not echos HTML, it is just a test, that is still running
     */
    public function testVarDump()
    {
        $this->object->debugOff();
        $this->object->setLastResult(['test 1']);
        $this->assertNotEmpty($this->object->varDump($this->object->getLast_Result()));
        $this->object->debugOn();
        $this->expectOutputRegex('/[Last Function Call]/');
        $this->object->varDump('');
    }

    public function testDump_var()
    {
        $this->object->setDebugEchoIsOn(true);
        $this->object->setLastResult(['Test 1', 'Test 2']);
        $this->expectOutputRegex('/[Last Function Call]/');
        $this->object->dump_var();
    }

    public function testDebug()
    {
        $this->object->setDebug_Echo_Is_On(true);
        $this->assertNotEmpty($this->object->debug(false));

        // In addition of getting a result, it fills the console
        $this->object->setLastError("test last");
        $this->expectOutputRegex('/[test last]/');
        $this->object->debug();
        $this->object->setFromDiskCache(true);
        $this->expectOutputRegex('/[Results retrieved from disk cache]/');
        $this->object->debug();
        $this->object->setColInfo(["just another test"]);
        $this->object->debug(false);
        $this->object->setColInfo(null);
        $this->object->setLast_Result(["just another test II"]);
        $this->object->debug(false);
    }

    public function testTimer_get_cur()
    {
        list($usec, $sec) = explode(' ', microtime());

        $expected = ((float) $usec + (float) $sec);

        $this->assertGreaterThanOrEqual($expected, $this->object->timer_get_cur());
    }

    public function testTimer_start()
    {
        $this->object->timer_start('test_timer');
        $this->assertNotNull($this->object->getTimers());
    }

    public function testTimer_elapsed()
    {
        $expected = 0;
        $this->object->timer_start('test_timer');
        usleep(5);
        $this->assertGreaterThanOrEqual($expected, $this->object->timer_elapsed('test_timer'));
    }

    public function testTimer_update_global()
    {
        $this->object->timer_start('test_timer');
        usleep(5);
        $this->object->setDoProfile(true);
        $this->object->timer_update_global('test_timer');
        $expected = $this->object->getTotalQueryTime();
        $this->assertGreaterThanOrEqual($expected, $this->object->timer_elapsed('test_timer'));
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->object->count());
        $this->object->count(true, true);
        $this->assertEquals(1, $this->object->count());
        $this->assertEquals(2, $this->object->count(false, true));
    }

    public function testAffectedRows()
    {
        $this->assertEquals(0, $this->object->affectedRows());
    }

    public function testIsConnected()
    {
        $this->assertFalse($this->object->isConnected());
    }

    public function test__Construct()
    {
        $ezsqlModel = $this->getMockBuilder(ezsqlModel::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertNull($ezsqlModel->__construct());
    }
}
