<?php

require_once 'shared/ez_sql_core.php';

require 'vendor/autoload.php';
use PHPUnit\Framework\TestCase;

/**
 * Test class for ezSQL_oracle8_9.
 * Desc..: Oracle 8 + 9 component (part of ezSQL databse abstraction library)
 * 
 * @author  Justin Vincent (jv@jvmultimedia.com)
 * @author  Stefanie Janine Stoelting <mail@stefanie-stoelting.de>
 * @link    http://twitter.com/justinvincent
 * @name    ezSQL_oracle8_9Test
 * @package ezSQL
 * @subpackage unitTests
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 * @todo The connection to Oracle is not tested by now. There might also be 
 *       tests done for different versions of Oracle
 *
 */
class ezSQL_oracle8_9Test extends TestCase {

    /**
     * @var ezSQL_oracle8_9
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        if (!extension_loaded('oci8_12c')) {
            $this->markTestSkipped(
              'The Oracle OCI Lib is not available.'
            );
        }
        require_once 'oracle8_9/ez_sql_oracle8_9.php';
        $this->object = new ezSQL_oracle8_9;
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        $this->object = null;
    } // tearDown

    /**
     * @covers ezSQL_oracle8_9::connect
     * @todo Implement testConnect().
     */
    public function testConnect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testConnect

    /**
     * @covers ezSQL_oracle8_9::quick_connect
     * @todo Implement testQuick_connect().
     */
    public function testQuick_connect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testQuick_connect

    /**
     * @covers ezSQL_oracle8_9::select
     * @todo Implement testSelect().
     */
    public function testSelect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testSelect

    /**
     * @covers ezSQL_oracle8_9::escape
     */
    public function testEscape() {
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testEscape

    /**
     * @covers ezSQL_oracle8_9::sysdate
     */
    public function testSysdate() {
        $this->assertEquals('SYSDATE', $this->object->sysdate());
    } // testSysdate

    /**
     * @covers ezSQL_oracle8_9::is_equal_str
     */
    public function testIs_equal_str() {
        $expected = '= \'ezTest string\'';
        
        $this->assertEquals($expected, $this->object->is_equal_str('ezTest string'));
    } // testIs_equal_str

    /**
     * @covers ezSQL_oracle8_9::is_equal_int
     */
    public function testIs_equal_int() {
        $expected = '= 123';
        
        $this->assertEquals($expected, $this->object->is_equal_int(123));
    } // testIs_equal_int

    /**
     * @covers ezSQL_oracle8_9::insert_id
     * @todo Implement testInsert_id().
     */
    public function testInsert_id() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testInsert_id

    /**
     * @covers ezSQL_oracle8_9::nextVal
     * @todo Implement testNextVal().
     */
    public function testNextVal() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testNextVal

    /**
     * @covers ezSQL_oracle8_9::query
     * @todo Implement testQuery().
     */
    public function testQuery() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testQuery

    /**
     * @covers ezSQL_oracle8_9::disconnect
     * @todo Implement testDisconnect().
     */
    public function testDisconnect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testDisconnect

    /**
     * @covers ezSQL_oracle8_9::getDBName
     * @todo Implement testGetDBName().
     */
    public function testGetDBName() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testGetDBName

} // ezSQL_oracle8_9Test