<?php
require_once('ez_sql_loader.php');

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
 * @subpackage Tests
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
        if (!extension_loaded('oci8_12c')) {
            $this->markTestSkipped(
              'The Oracle OCI Lib is not available.'
            );
        }
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
    
    /**
     * @covers ezSQLcore::get_var
     */
    public function testGet_var() { 
        $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME); 
        // Demo of getting a single variable from the db
        // (and using abstracted function sysdate)   
        $current_time = $this->object->get_var("SELECT " . $this->object->sysdate() . " FROM DUAL");
        $this->assertNotNull($current_time);
    } // testGet_var

    /**
     * @covers ezSQLcore::get_results
     */
    public function testGet_results() {           
    $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);    
    
	// Get list of tables from current database..
	$my_tables = $this->object->get_results("SELECT TABLE_NAME FROM USER_TABLES",ARRAY_N);
    $this->assertNotNull($my_tables);
    
	// Loop through each row of results..
	foreach ( $my_tables as $table )
        {
            // Get results of DESC table..
            $this->assertNotNull($this->object->get_results("SELECT COLUMN_NAME, DATA_TYPE, DATA_LENGTH, DATA_PRECISION FROM USER_TAB_COLUMNS WHERE TABLE_NAME = '$table[0]'"));
        }
    } // testGet_results
    
    /**
     * @covers ezSQL_oracle8_9::__construct
     */
    public function test__Construct() {   
        $this->errors = array();
        set_error_handler(array($this, 'errorHandler'));    
        
        $oracle8_9 = $this->getMockBuilder(ezSQL_oracle8_9::class)
        ->setMethods(null)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->assertNull($oracle8_9->__construct());  
    } 
    
} // ezSQL_oracle8_9Test