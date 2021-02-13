<?php

namespace ezsql\Tests\mysqli;

use ezsql\ezResultset;
use ezsql\Tests\EZTestCase;

use function ezsql\functions\{
    mysqlInstance,
    column,
};

class ezResultsetTest extends EZTestCase
{
    /**
     * @var ezResultset
     */
    protected $object;

    /**
     * database connection
     * @var object
     */
    protected $database = null;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped('The MySQL Lib is not available.');
        }

        $this->database = mysqlInstance([self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);

        $this->database->drop('unit_test');
        $this->database->create(
            'unit_test',
            column('id', INTR, 11, PRIMARY),
            column('test_key', VARCHAR, 50)
        );

        $this->database->insert('unit_test', ['id' => 1, 'test_key' => 'test 1']);
        $this->database->insert('unit_test', ['id' => 2, 'test_key' => 'test 2']);
        $this->database->insert('unit_test', ['id' => 3, 'test_key' => 'test 3']);
        $this->database->insert('unit_test', ['id' => 4, 'test_key' => 'test 4']);
        $this->database->insert('unit_test', ['id' => 5, 'test_key' => 'test 5']);

        $this->database->select('unit_test');

        $this->object = new ezResultset($this->database->get_results());
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->database->drop('unit_test');
        $this->object = null;
    } // tearDown

    public function testRewind()
    {
        for ($index = 0; $index < 3; $index++) {
            $result = $this->object->fetch_object();

            $this->assertEquals($index + 1, $result->id);
        }

        $this->object->rewind();
        $result = $this->object->fetch_object();
        $this->assertEquals(1, $result->id);
    }

    public function testCurrent()
    {
        $result = $this->object->current(ezResultset::RESULT_AS_OBJECT);

        $this->assertTrue(is_a($result, 'stdClass'));

        $this->assertEquals(1, $result->id);
    }

    public function testKey()
    {
        $this->assertEquals(0, $this->object->key());

        $this->object->fetch_object();

        $this->assertEquals(1, $this->object->key());
    }

    public function testNext()
    {
        $this->object->current(ezResultset::RESULT_AS_OBJECT);
        $this->assertEquals(0, $this->object->key());

        $this->object->next();
        $this->assertEquals(1, $this->object->key());
    }

    public function testPrevious()
    {
        $this->object->current(ezResultset::RESULT_AS_OBJECT);
        $this->object->next();
        $this->object->next();
        $this->assertEquals(2, $this->object->key());

        $this->object->previous();
        $this->assertEquals(1, $this->object->key());
    }

    public function testValid()
    {
        $this->assertTrue($this->object->valid());
    }

    public function testFetch_assoc()
    {
        $result = $this->object->fetch_assoc();

        $this->assertTrue(is_array($result));

        $this->assertEquals(1, $result['id']);
    }

    public function testFetch_row()
    {
        $result = $this->object->fetch_row();

        $this->assertTrue(is_array($result));

        $this->assertEquals(1, $result[0]);
    }

    public function testFetch_object()
    {
        $result = $this->object->fetch_object();

        $this->assertTrue(is_a($result, 'stdClass'));

        $this->assertEquals(1, $result->id);
    }

    public function test__Construct()
    {
        $resultset = $this->getMockBuilder(ezResultset::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->expectExceptionMessage('testuser is not valid.');
        $this->assertNull($resultset->__construct('testuser'));
    }
}
