<?php
require_once('ez_sql_loader.php');

require 'vendor/autoload.php';
use PHPUnit\Framework\TestCase;

/**
 * Test class for ezSQL_odbc.
 * Desc..: MS SQL Server component (part of ezSQL databse abstraction library)
 * 
 * @author  Justin Vincent (jv@jvmultimedia.com)
 * @author  Stefanie Janine Stoelting <mail@stefanie-stoelting.de>
 * @author  Lawrence Stubbs (technoexpressnet@gmail.com)
 * @link    http://twitter.com/justinvincent
 * @name    ezSQL_odbcTest
 * @package ezSQL
 * @subpackage Tests
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 * @todo The connection to MS SQL Server is not tested by now. There might also
 *       be tests done for different versions of SQL Server
 *
 */
class ezSQL_odbcTest extends TestCase {

    /**
     * @var ezSQL_odbc
     */
    protected $object;
    private $errors;
 
    function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
        $this->errors[] = compact("errno", "errstr", "errfile",
            "errline", "errcontext");
    }

    function assertError($errstr, $errno) {
        foreach ($this->errors as $error) {
            if ($error["errstr"] === $errstr
                && $error["errno"] === $errno) {
                return;
            }
        }
        $this->fail("Error with level " . $errno .
            " and message '" . $errstr . "' not found in ", 
            var_export($this->errors, TRUE));
    }   

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        if (!extension_loaded('odbc')) {
            $this->markTestSkipped(
              'The MS-SQL extenstion is not available.'
            );
        }
        $this->object = new ezSQL_odbc;
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        $this->object = null;
    } // tearDown

    /**
     * @covers ezSQL_odbc::quick_connect
     * @todo Implement testQuick_connect().
     */
    public function testQuick_connect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testQuick_connect

    /**
     * @covers ezSQL_odbc::connect
     * @todo Implement testConnect().
     */
    public function testConnect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testConnect

    /**
     * @covers ezSQL_odbc::select
     * @todo Implement testSelect().
     */
    public function testSelect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testSelect

    /**
     * @covers ezSQL_odbc::escape
     */
    public function testEscape() {
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testEscape

    /**
     * @covers ezSQL_odbc::sysdate
     */
    public function testSysdate() {
        $this->assertEquals('GETDATE()', $this->object->sysdate());
    } // testSysdate

    /**
     * @covers ezSQL_odbc::query
     * @todo Implement testQuery().
     */
    public function testQuery() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testQuery

    /**
     * @covers ezSQL_odbc::ConvertMySqlToodbc
     * @todo Implement testConvertMySqlToodbc().
     */
    public function testConvertMySqlToodbc() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testConvert

    /**
     * @covers ezSQL_odbc::disconnect
     * @todo Implement testDisconnect().
     */
    public function testDisconnect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testDisconnect

    /**
     * @covers ezSQL_odbc::getDBHost
     * @todo Implement testGetDBHost().
     */
    public function testGetDBHost() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    } // testGetDBHost
    
    /**
     * @covers ezSQL_odbc::__construct
     */
    public function test__Construct() {   
        $this->errors = array();
        set_error_handler(array($this, 'errorHandler'));    
        
        $odbc = $this->getMockBuilder(ezSQL_odbc::class)
        ->setMethods(null)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->assertNull($odbc->__construct());  
    } 
    
} // ezSQL_odbcTest
