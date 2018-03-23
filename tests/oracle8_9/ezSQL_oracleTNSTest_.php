<?php
require_once('ez_sql_loader.php');

require 'vendor/autoload.php';
use PHPUnit\Framework\TestCase;

/**
 * Test class for ezSQL_oracleTNS.
 * Desc..: Oracle TNS component (part of ezSQL databse abstraction library)
 *
 * @author  Justin Vincent (jv@jvmultimedia.com)
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @link    http://twitter.com/justinvincent
 * @name    ezSQL_oracleTNSTest
 * @package ezSQL
 * @subpackage Tests
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 */
class ezSQL_oracleTNSTest extends TestCase {

    /**
     * @var ezSQL_oracleTNS
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
     * The connection parameters for the Oracle DB connection
     * @var array
     */
    private $oraConnectionParamsTestConnection = array(
        'User'          => 'CMP',
        'Password'      => 'cmp',
        'Host'          => 'en-yoda-1',
        'Port'          => '1521',
        'SessionName'   => 'ppisa.febi.bilstein.local',
        'TNS'           => 'AL32UTF8'
    );

    private $sequenceName = 'UNITTEST_ORATNS';

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
        $this->object = new ezSQL_oracleTNS(
                    $this->oraConnectionParamsTestConnection['Host'],
                    $this->oraConnectionParamsTestConnection['Port'],
                    $this->oraConnectionParamsTestConnection['SessionName'],
                    $this->oraConnectionParamsTestConnection['User'],
                    $this->oraConnectionParamsTestConnection['Password'],
                    $this->oraConnectionParamsTestConnection['TNS']
                );

        // Create the sequence
        $sql = 'CREATE SEQUENCE ' . $this->sequenceName;
        $this->object->query($sql);

    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        // Drop the sequence
        //$sql = 'DROP SEQUENCE ' . $this->sequenceName;
        //$this->object->query($sql);

        $this->object = null;
    } // tearDown

    /**
     * @covers ezSQL_oracleTNS::connect
     */
    public function testConnect() {
        $this->object->connect(
                    $this->oraConnectionParamsTestConnection['User'],
                    $this->oraConnectionParamsTestConnection['Password']
                );
        $this->assertTrue($this->object->isConnected());
    } // testConnect

    /**
     * To test connection pooling with oci_pconnect instead of oci_connect
     * @covers ezSQL_oracleTNS::connect
     */
    public function testPConnect() {
        $this->object = null;

        $this->object = new ezSQL_oracleTNS(
                    $this->oraConnectionParamsTestConnection['Host'],
                    $this->oraConnectionParamsTestConnection['Port'],
                    $this->oraConnectionParamsTestConnection['SessionName'],
                    $this->oraConnectionParamsTestConnection['User'],
                    $this->oraConnectionParamsTestConnection['Password'],
                    $this->oraConnectionParamsTestConnection['TNS'],
                    true
                );

        $this->object->connect(
                    $this->oraConnectionParamsTestConnection['User'],
                    $this->oraConnectionParamsTestConnection['Password']
                );
        $this->assertTrue($this->object->isConnected());

        $sql = 'SELECT 5*5 AS TEST_RESULT FROM DUAL';

        $recordset = $this->object->query($sql);
        $this->assertEquals(1, $recordset);
    } // testPConnect

    /**
     * @covers ezSQL_oracleTNS::quick_connect
     */
    public function testQuick_connect() {
        $this->object->quick_connect(
                    $this->oraConnectionParamsTestConnection['User'],
                    $this->oraConnectionParamsTestConnection['Password']
                );
        $this->assertTrue(true);
    } // testQuick_connect

    /**
     * @covers ezSQL_oracleTNS::select
     */
    public function testSelect() {
        $this->object->select(
                    $this->oraConnectionParamsTestConnection['User'],
                    $this->oraConnectionParamsTestConnection['Password']
                );
        $this->assertTrue(true);
    } // testSelect

    /**
     * @covers ezSQL_oracleTNS::escape
     */
    public function testEscape() {
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is''nt escaped.", $result);
    } // testEscape

    /**
     * @covers ezSQL_oracleTNS::sysdate
     */
    public function testSysdate() {
        $this->assertEquals('SYSDATE', $this->object->sysdate());
    } // testSysdate

    /**
     * @covers ezSQL_oracleTNS::is_equal_str
     */
    public function testIs_equal_str() {
        $expected = '= \'ezTest string\'';

        $this->assertEquals($expected, $this->object->is_equal_str('ezTest string'));
    } // testIs_equal_str

    /**
     * @covers ezSQL_oracleTNS::is_equal_int
     */
    public function testIs_equal_int() {
        $expected = '= 123';

        $this->assertEquals($expected, $this->object->is_equal_int(123));
    } // testIs_equal_int

    /**
     * @covers ezSQL_oracleTNS::insert_id
     */
    public function testInsert_id() {
        $this->object->connect(
                    $this->oraConnectionParamsTestConnection['User'],
                    $this->oraConnectionParamsTestConnection['Password']
                );

        $result = $this->object->insert_id($this->sequenceName);

        $this->assertEquals(1, $result);

        $result = $this->object->insert_id($this->sequenceName);

        $this->assertEquals(2, $result);
    } // testInsert_id

    /**
     * @covers ezSQL_oracleTNS::nextVal
     */
    public function testNextVal() {
        $this->object->connect(
                    $this->oraConnectionParamsTestConnection['User'],
                    $this->oraConnectionParamsTestConnection['Password']
                );
        $result = $this->object->nextVal($this->sequenceName);

        $this->assertEquals(1, $result);

        $result = $this->object->nextVal($this->sequenceName);

        $this->assertEquals(2, $result);
    } // testNextVal

    /**
     * @covers ezSQL_oracleTNS::query
     */
    public function testQuery() {
        $this->object->connect(
                    $this->oraConnectionParamsTestConnection['User'],
                    $this->oraConnectionParamsTestConnection['Password']
                );

        $sql = 'SELECT 5*5 AS TEST_RESULT FROM DUAL';

        $recordset = $this->object->query($sql);
        $this->assertEquals(1, $recordset);
    } // testQuery

    /**
     * @covers ezSQL_oracleTNS::disconnect
     */
    public function testDisconnect() {
        $this->object->disconnect();

        $this->assertFalse($this->object->isConnected());
    } // testDisconnect
       
    /**
     * @covers ezSQL_oracleTNS::__construct
     */
    public function test__Construct() {            
        $oracle = $this->getMockBuilder(ezSQL_oracleTNS::class)
        ->setMethods(null)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->assertNull($oracle->__construct());  
    } 
} // ezSQL_oracleTNSTest