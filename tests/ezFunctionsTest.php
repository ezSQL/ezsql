<?php

namespace ezsql\Tests;

use ezsql\Tests\EZTestCase;

class ezFunctionsTest extends EZTestCase
{
    protected function setUp(): void
	{
        \clearInstance();
    }

    public function testGetInstance()
    {
        $this->assertNull(getInstance());
    }

    public function testGetVendor()
    {
        $this->assertNull(getVendor());
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

    public function testEq()
    {
        $this->assertInternalType('array', eq('field', 'data'));
        $this->assertArraySubset([1 => EQ], eq('field', 'data'));
    }

    public function testNeq()
    {
        $this->assertInternalType('array', neq('field', 'data'));
        $this->assertArraySubset([3 => _AND], neq('field', 'data', _AND));
    }

    public function testNe()
    {
        $this->assertInternalType('array', ne('field', 'data'));
        $this->assertArraySubset([4 => 'extra'], ne('field', 'data', _AND, 'extra'));
    }

    public function testLt()
    {
        $this->assertInternalType('array', lt('field', 'data'));
        $this->assertArraySubset([2 => 'data'], lt('field', 'data'));
    }

    public function testLte()
    {
        $this->assertInternalType('array', lte('field', 'data'));
        $this->assertArraySubset([0 => 'field'], lte('field', 'data'));
    }

    public function testGt()
    {
        $this->assertInternalType('array', gt('field', 'data'));
        $this->assertArraySubset([0 => 'field'], gt('field', 'data'));
    }

    public function testGte()
    {
        $this->assertInternalType('array', gte('field', 'data'));
        $this->assertArraySubset([0 => 'field'], gte('field', 'data'));
    }

    public function testIsNull()
    {
        $this->assertInternalType('array', isNull('field'));
        $this->assertArraySubset([2 => 'null'], isNull('field'));
    }

    public function testIsNotNull()
    {
        $this->assertInternalType('array', isNotNull('field'));
        $this->assertArraySubset([2 => 'null'], isNotNull('field'));
    }

    public function testLike()
    {
        $this->assertInternalType('array', like('field', 'data'));
        $this->assertArraySubset([2 => 'data'], like('field', 'data'));
    }

    public function testNotLike()
    {
        $this->assertInternalType('array', notLike('field', 'data'));
        $this->assertArraySubset([2 => 'data'], notLike('field', 'data'));
    }

    public function testIn()
    {
        $this->assertInternalType('array', in('field', 'data'));
        $this->assertArraySubset([8 => 'data6'], in('field', 'data', 'data1', 'data2', 'data3', 'data4', 'data5', 'data6'));
    }

    public function testNotIn()
    {
        $this->assertInternalType('array', notIn('field', 'data'));
        $this->assertArraySubset([5 => 'data3'], notIn('field', 'data', 'data1', 'data2', 'data3', 'data4', 'data5', 'data6'));
    }

    public function testBetween()
    {
        $this->assertInternalType('array', between('field', 'data', 'data2'));
        $this->assertArraySubset([1 => _BETWEEN], between('field', 'data', 'data2'));
    }

    public function testNotBetween()
    {
        $this->assertInternalType('array', notBetween('field', 'data', 'data2'));
        $this->assertArraySubset([3 => 'data2'], notBetween('field', 'data', 'data2'));
    }

    public function testSetInstance() {
        $this->assertFalse(\setInstance());
        $this->assertFalse(\setInstance($this));
    }

    public function testSelect() {
        $this->assertFalse(select(''));
    }

    public function testSelect_into() {
        $this->assertFalse(select_into('field', 'data', 'data2'));
    }

    public function testInsert_select() {
        $this->assertFalse(insert_select('field', 'data', 'data2'));
    }

    public function testCreate_select() {
        $this->assertFalse(create_select('field', 'data', 'data2'));
    }

    public function testWhere() {
        $this->assertFalse(where('field', 'data', 'data2'));
    }

    public function testGroupBy() {
        $this->assertFalse(groupBy(''));
        $this->assertNotNull(groupBy('field'));
    }

    public function testHaving() {
        $this->assertFalse(having('field', 'data', 'data2'));
    }

    public function testOrderBy() {
        $this->assertFalse(orderBy('', 'data'));
        $this->assertNotNull(orderBy('field', 'data'));
    }

    public function testInsert() {
        $this->assertFalse(insert('field', ['data' => 'data2']));
    }

    public function testUpdate() {
        $this->assertFalse(update('field', 'data', 'data2'));
    }

    public function testDeleting() {
        $this->assertFalse(deleting('field', 'data', 'data2'));
    }

    public function testReplace() {
        $this->assertFalse(replace('field', ['data' => 'data2']));
    }
}
