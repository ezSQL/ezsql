<?php

require_once dirname(__FILE__) . '/../../../shared/ez_sql_core.php';
require_once dirname(__FILE__) . '/../../../mysql/ez_sql_mysql.php';

/**
 * Test class for ezSQL_mysql.
 * Generated by PHPUnit
 *
 * Needs database tear up to run test, that creates database and a user with
 * appropriate rights.
 * Run database tear down after tests to get rid of the database and the user.
 *
 * @author  Stefanie Janine Stoelting <mail@stefanie-stoelting.de>
 * @name    ezSQL_mysql_tear_up
 * @uses    mysql_test_db_tear_up.sql
 * @uses    mysql_test_db_tear_down.sql
 * @package ezSQL
 * @subpackage unitTests
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 */
class ezSQL_mysqlTest extends PHPUnit_Framework_TestCase {

    /**
     * constant string user name
     */
    const TEST_DB_USER = 'ez_test';

    /**
     * constant string password
     */
    const TEST_DB_PASSWORD = 'ezTest';

    /**
     * constant database name
     */
    const TEST_DB_NAME = 'ez_test';

    /**
     * constant database host
     */
    const TEST_DB_HOST = 'localhost';

    /**
     * constant database connection charset
     */
    const TEST_DB_CHARSET = 'utf8';

    /**
     * @var ezSQL_mysql
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new ezSQL_mysql;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        $this->object = null;
    }

    /**
     * @covers ezSQL_mysql::quick_connect
     */
    public function testQuick_connect() {
        $result = $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME);

        $this->assertTrue($result);
    }

    /**
     * @covers ezSQL_mysql::connect
     */
    public function testConnect() {
        $result = $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);

        $this->assertTrue($result);
    } // testConnect

    /**
     * @covers ezSQL_mysql::select
     */
    public function testSelect() {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $this->assertTrue($this->object->isConnected());

        $result = $this->object->select(self::TEST_DB_NAME);

        $this->assertTrue($result);
    } // testSelect

    /**
     * @covers ezSQL_mysql::escape
     */
    public function testEscape() {
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is\\'nt escaped.", $result);
    } // testEscape

    /**
     * @covers ezSQL_mysql::sysdate
     */
    public function testSysdate() {
        $this->assertEquals('NOW()', $this->object->sysdate());
    } // testSysdate

    /**
     * @covers ezSQL_mysql::query
     */
    public function testQueryInsert() {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);

        $this->object->select(self::TEST_DB_NAME);

        $this->assertEquals($this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'), 0);
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(1, \'test 1\')'), 1);
        $this->assertEquals($this->object->query('DROP TABLE unit_test'), 0);
    } // testQueryInsert

    /**
     * @covers ezSQL_mysql::query
     */
    public function testQuerySelect() {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);

        $this->object->select(self::TEST_DB_NAME);
        
        $this->assertEquals($this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'), 0);
        
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(1, \'test 1\')'), 1);
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(2, \'test 2\')'), 1);
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(3, \'test 3\')'), 1);
        
        $result = $this->object->query('SELECT * FROM unit_test');
        
        $i = 1;
        foreach ($this->object->get_results() as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('test ' . $i, $row->test_key);
            ++$i;
        }
        
        $this->assertEquals($this->object->query('DROP TABLE unit_test'), 0);
    } // testQuerySelect

    /**
     * @covers ezSQL_mysql::getDBHost
     */
    public function testGetDBHost() {
        $this->assertEquals(self::TEST_DB_HOST, $this->object->getDBHost());
    } // testGetDBHost

    /**
     * @covers ezSQL_mysql::getCharset
     */
    public function testGetCharset() {
        $this->assertEquals(self::TEST_DB_CHARSET, $this->object->getCharset());
    } // testGetCharset

    /**
     * @covers ezSQL_mysql::disconnect
     */
    public function testDisconnect() {
        $this->object->disconnect();

        $this->assertTrue(true);
    } // testDisconnect

    /**
     * @covers ezSQL_mysql::getInsertId 
     */
    public function testGetInsertId() {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);

        $this->object->select(self::TEST_DB_NAME);

        $this->assertEquals($this->object->query('CREATE TABLE unit_test(id int(11) NOT NULL AUTO_INCREMENT, test_key varchar(50), PRIMARY KEY (ID))ENGINE=MyISAM  DEFAULT CHARSET=utf8'), 0);
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(1, \'test 1\')'), 1);
        
        $this->assertEquals(1, $this->object->getInsertId($this->object->dbh));
        
        $this->assertEquals($this->object->query('DROP TABLE unit_test'), 0);
    } // testInsertId
     
} // ezSQL_mysqlTest