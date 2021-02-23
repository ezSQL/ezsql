<?php

namespace ezsql\Tests;

use ezsql\Tests\EZTestCase;

use function ezsql\functions\{
    setInstance,
    getInstance,
    clearInstance,
    get_vendor,
    column,
    primary,
    foreign,
    unique,
    index,
    addColumn,
    dropColumn,
    changingColumn,
    eq,
    neq,
    ne,
    lt,
    lte,
    gt,
    gte,
    isNull,
    isNotNull,
    like,
    in,
    notLike,
    notIn,
    between,
    notBetween,
    select_into,
    insert_select,
    create_select,
    where,
    groupBy,
    having,
    orderBy,
    updating,
    creating,
    deleting,
    dropping,
    replacing,
    selecting,
    inserting,
    altering,
    get_results,
    table_setup,
    set_table,
    set_prefix,
    clean_string,
    is_traversal,
    sanitize_path
};

class ezFunctionsTest extends EZTestCase
{
    protected function setUp(): void
    {
        clearInstance();
    }

    public function testClean_string()
    {
        $this->assertEquals("' help", clean_string("<?php echo 'foo' >' help</php?>"));
    }

    public function testSanitize_path()
    {
        $this->assertEquals("config.php", sanitize_path("../../../../config.php-"));
    }

    public function testis_traversal()
    {
        $this->assertEquals(true, is_traversal('/home', "../../../../config.php"));
        $this->assertEquals(true, is_traversal(__DIR__, dirname(__DIR__), 8));
        $this->assertEquals(false, is_traversal(__DIR__, 'Foo.php'));
    }

    public function testGetInstance()
    {
        $this->assertNull(getInstance());
    }

    public function testGetVendor()
    {
        $this->assertNull(get_vendor());
    }

    public function testColumn()
    {
        $this->assertFalse(column('string', VARCHAR, 32));
    }

    public function testPrimary()
    {
        $this->assertFalse(primary('label', 'column'));
    }

    public function testForeign()
    {
        $this->assertFalse(foreign('label', 'column'));
    }

    public function testUnique()
    {
        $this->assertFalse(unique('label', 'column'));
    }

    public function testIndex()
    {
        $this->assertFalse(index('label', 'column'));
    }

    public function testAddColumn()
    {
        $this->assertFalse(addColumn('column', VARCHAR, 32));
    }

    public function testDropColumn()
    {
        $this->assertFalse(dropColumn('column', 'column'));
    }
    public function testChangingColumn()
    {
        $this->assertFalse(changingColumn('column', 'column'));
    }

    public function testEq()
    {
        $this->assertIsArray(eq('field', 'data'));
        $this->assertArraySubset([1 => EQ], eq('field', 'data'));
    }

    public function testNeq()
    {
        $this->assertIsArray(neq('field', 'data'));
        $this->assertArraySubset([3 => _AND], neq('field', 'data', _AND));
    }

    public function testNe()
    {
        $this->assertIsArray(ne('field', 'data'));
        $this->assertArraySubset([4 => 'extra'], ne('field', 'data', _AND, 'extra'));
    }

    public function testLt()
    {
        $this->assertIsArray(lt('field', 'data'));
        $this->assertArraySubset([2 => 'data'], lt('field', 'data'));
    }

    public function testLte()
    {
        $this->assertIsArray(lte('field', 'data'));
        $this->assertArraySubset([0 => 'field'], lte('field', 'data'));
    }

    public function testGt()
    {
        $this->assertIsArray(gt('field', 'data'));
        $this->assertArraySubset([0 => 'field'], gt('field', 'data'));
    }

    public function testGte()
    {
        $this->assertIsArray(gte('field', 'data'));
        $this->assertArraySubset([0 => 'field'], gte('field', 'data'));
    }

    public function testIsNull()
    {
        $this->assertIsArray(isNull('field'));
        $this->assertArraySubset([2 => 'null'], isNull('field'));
    }

    public function testIsNotNull()
    {
        $this->assertIsArray(isNotNull('field'));
        $this->assertArraySubset([2 => 'null'], isNotNull('field'));
    }

    public function testLike()
    {
        $this->assertIsArray(like('field', 'data'));
        $this->assertArraySubset([2 => 'data'], like('field', 'data'));
    }

    public function testNotLike()
    {
        $this->assertIsArray(notLike('field', 'data'));
        $this->assertArraySubset([2 => 'data'], notLike('field', 'data'));
    }

    public function testIn()
    {
        $this->assertIsArray(in('field', 'data'));
        $this->assertArraySubset([8 => 'data6'], in('field', 'data', 'data1', 'data2', 'data3', 'data4', 'data5', 'data6'));
    }

    public function testNotIn()
    {
        $this->assertIsArray(notIn('field', 'data'));
        $this->assertArraySubset([5 => 'data3'], notIn('field', 'data', 'data1', 'data2', 'data3', 'data4', 'data5', 'data6'));
    }

    public function testBetween()
    {
        $this->assertIsArray(between('field', 'data', 'data2'));
        $this->assertArraySubset([1 => _BETWEEN], between('field', 'data', 'data2'));
    }

    public function testNotBetween()
    {
        $this->assertIsArray(notBetween('field', 'data', 'data2'));
        $this->assertArraySubset([3 => 'data2'], notBetween('field', 'data', 'data2'));
    }

    public function testSetInstance()
    {
        $this->assertFalse(setInstance());
    }

    public function testSelect_into()
    {
        $this->assertFalse(select_into('field', 'data', 'data2'));
    }

    public function testInsert_select()
    {
        $this->assertFalse(insert_select('field', 'data', 'data2'));
    }

    public function testCreate_select()
    {
        $this->assertFalse(create_select('field', 'data', 'data2'));
    }

    public function testWhere()
    {
        $this->assertFalse(where('field', 'data', 'data2'));
    }

    public function testGroupBy()
    {
        $this->assertFalse(groupBy(''));
        $this->assertNotNull(groupBy('field'));
    }

    public function testHaving()
    {
        $this->assertFalse(having('field', 'data', 'data2'));
    }

    public function testOrderBy()
    {
        $this->assertFalse(orderBy('', 'data'));
        $this->assertNotNull(orderBy('field', 'data'));
    }

    public function testUpdating()
    {
        $this->assertFalse(updating(['data', 'data2']));
    }

    public function testCreating()
    {
        $this->assertFalse(creating(['data', 'data2']));
    }

    public function testDeleting()
    {
        $this->assertFalse(deleting('field', 'data', 'data2'));
    }

    public function testInserting()
    {
        $this->assertFalse(inserting([]));
    }

    public function testTable_Setup()
    {
        $this->assertFalse(table_setup());
    }

    public function testSelecting()
    {
        $this->assertFalse(selecting());
    }

    public function testReplacing()
    {
        $this->assertFalse(replacing(['data' => 'data2']));
    }

    public function testAltering()
    {
        $this->assertFalse(altering([]));
    }

    public function testGet_results()
    {
        $this->assertFalse(get_results());
    }

    public function testDropping()
    {
        $this->assertFalse(dropping());
    }

    public function testSet_Table()
    {
        $this->assertFalse(set_table());
    }

    public function testSet_Prefix()
    {
        $this->assertFalse(set_prefix());
    }
}
