<?php

require_once('ez_sql_loader.php');

require 'vendor/autoload.php';
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

/**
 * Test class for ezSQL_sqlsrv.
 * Desc..: Microsoft Sql Server component (MS drivers) (part of ezSQL databse abstraction library)
 * 
 * @author  Justin Vincent (jv@jvmultimedia.com)
 * @author  Stefanie Janine Stoelting <mail@stefanie-stoelting.de>
 * @link    http://twitter.com/justinvincent
 * @name    ezSQL_sqlsrvTest
 * @package ezSQL
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 * @todo The connection to sqlsrv is not tested by now. There might also
 *       be tests done for different versions of sqlsrv
 *
 */
class ezSQL_sqlsrvTest extends TestCase {

    /**
     * @var ezSQL_sqlsrv
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        if (!extension_loaded('ntwdblib')) {
            $this->markTestSkipped(
              'The sqlsrv Lib is not available.'
            );
        }
        $this->object = new ezSQL_sqlsrv;
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        $this->object = null;
    } // tearDown

    /**
     * @covers ezSQL_sqlsrv::quick_connect
     * @todo Implement testQuick_connect().
     */
    public function testQuick_connect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testQuick_connect

    /**
     * @covers ezSQL_sqlsrv::connect
     * @todo Implement testConnect().
     */
    public function testConnect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testConnect

    /**
     * @covers ezSQL_sqlsrv::select
     * @todo Implement testSelect().
     */
    public function testSelect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testSelect

    /**
     * @covers ezSQL_sqlsrv::escape
     */
    public function testEscape() {
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testEscape

    /**
     * @covers ezSQL_sqlsrv::sysdate
     */
    public function testSysdate() {
        $this->assertEquals('getDate()', $this->object->sysdate());
    } // testSysdate

    /**
     * @covers ezSQL_sqlsrv::query
     * @todo Implement testQuery().
     */
    public function testQuery() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testQuery

    /**
     * @covers ezSQL_sqlsrv::ConvertMySqlTosybase
     * @todo Implement testConvertMySqlTosybase().
     */
    public function testConvertMySqlTosybase() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testConvertMySqlTosybase

    /**
     * @covers ezSQL_sqlsrv::disconnect
     * @todo Implement testDisconnect().
     */
    public function testDisconnect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testDisconnect

} // ezSQL_sqlsrvTest