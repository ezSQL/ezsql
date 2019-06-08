<?php

namespace ezsql\Tests;

use ezsql\Tests\EZTestCase;

class ezFunctionsTest extends EZTestCase 
{
    protected function setUp(): void
	{
        \clearInstance();
    }

    /**
     * @test getInstance 
     */
    public function testGetInstance()
    {
        $this->assertNull(getInstance());
    }
    
    /**
     * @test getVendor 
     */
    public function testGetVendor()
    {
        $this->assertNull(getVendor());
    }

    /**
     * @test column 
     */
    public function testColumn()
    {
        $this->assertFalse(column('string', VARCHAR, 32));
    }
    
    /**
     * @test primary 
     */
    public function testPrimary()
    {
        $this->assertFalse(primary('label', 'column'));
    }

    /**
     * @test foreign 
     */
    public function testForeign()
    {
        $this->assertFalse(foreign('label', 'column'));
    }

    /**
     * @test unique 
     */
    public function testUnique()
    {
        $this->assertFalse(unique('label', 'column'));
    }

    /**
     * @test index 
     */
    public function testIndex()
    {
        $this->assertFalse(index('label', 'column'));
    }

    /**
     * @test addColumn 
     */
    public function testAddColumn()
    {
        $this->assertFalse(addColumn('column', VARCHAR, 32));
    }

    /**
     * @test dropColumn 
     */
    public function testDropColumn()
    {
        $this->assertFalse(dropColumn('column', 'column'));
    }

    /**
     * @test eq 
     */
    public function testEq()
    {
        $this->assertInternalType('array', eq('field', 'data'));
        $this->assertArraySubset([1 => EQ], eq('field', 'data'));
    }

    /**
     * @test neq
     */
    public function testNeq()
    {
        $this->assertInternalType('array', neq('field', 'data'));
        $this->assertArraySubset([3 => _AND], neq('field', 'data', _AND));
    }

    /**
     * @test ne
     */
    public function testNe()
    {
        $this->assertInternalType('array', ne('field', 'data'));
        $this->assertArraySubset([4 => 'extra'], ne('field', 'data', _AND, 'extra'));
    }
    
    /**
     * @test lt
     */
    public function testLt()
    {
        $this->assertInternalType('array', lt('field', 'data'));
        $this->assertArraySubset([2 => 'data'], lt('field', 'data'));
    }

    /**
     * @test lte
     */
    public function testLte()
    {
        $this->assertInternalType('array', lte('field', 'data'));
        $this->assertArraySubset([0 => 'field'], lte('field', 'data'));
    }

    /**
     * @test gt
     */
    public function testGt()
    {
        $this->assertInternalType('array', gt('field', 'data'));
        $this->assertArraySubset([0 => 'field'], gt('field', 'data'));
    }

    /**
     * @test gte
     */
    public function testGte()
    {
        $this->assertInternalType('array', gte('field', 'data'));
        $this->assertArraySubset([0 => 'field'], gte('field', 'data'));
    }

    /**
     * @test isNull
     */
    public function testIsNull()
    {
        $this->assertInternalType('array', isNull('field'));
        $this->assertArraySubset([2 => 'null'], isNull('field'));
    }

    /**
     * @test isNotNull
     */
    public function testIsNotNull()
    {
        $this->assertInternalType('array', isNotNull('field'));
        $this->assertArraySubset([2 => 'null'], isNotNull('field'));
    }

    /**
     * @test like
     */
    public function testLike()
    {
        $this->assertInternalType('array', like('field', 'data'));
        $this->assertArraySubset([2 => 'data'], like('field', 'data'));
    }

    /**
     * @test notLike
     */
    public function testNotLike()
    {
        $this->assertInternalType('array', notLike('field', 'data'));
        $this->assertArraySubset([2 => 'data'], notLike('field', 'data'));
    }

    /**
     * @test in
     */
    public function testIn()
    {
        $this->assertInternalType('array', in('field', 'data'));
        $this->assertArraySubset([8 => 'data6'], in('field', 'data', 'data1', 'data2', 'data3', 'data4', 'data5', 'data6'));
    }

    /**
     * @test notIn
     */
    public function testNotIn()
    {
        $this->assertInternalType('array', notIn('field', 'data'));
        $this->assertArraySubset([5 => 'data3'], notIn('field', 'data', 'data1', 'data2', 'data3', 'data4', 'data5', 'data6'));
    }

    /**
     * @test between
     */
    public function testBetween()
    {
        $this->assertInternalType('array', between('field', 'data', 'data2'));
        $this->assertArraySubset([1 => _BETWEEN], between('field', 'data', 'data2'));
    }

    /**
     * @test notBetween
     */
    public function testNotBetween()
    {
        $this->assertInternalType('array', notBetween('field', 'data', 'data2'));
        $this->assertArraySubset([3 => 'data2'], notBetween('field', 'data', 'data2'));
    }

    /**
     * @test setInstance
     */
    public function testSetInstance() {
        $this->assertFalse(\setInstance());
        $this->assertFalse(\setInstance($this));
    }

    /**
     * @test select
     */
    public function testSelect() {
        $this->assertFalse(select(''));
    } 

    /**
     * @test select_into
     */    
    public function testSelect_into() {
        $this->assertFalse(select_into('field', 'data', 'data2'));
    } 

    /**
     * @test insert_select
     */    
    public function testInsert_select() {
        $this->assertFalse(insert_select('field', 'data', 'data2'));
    }     

    /**
     * @test create_select
     */    
    public function testCreate_select() {
        $this->assertFalse(create_select('field', 'data', 'data2'));
    }  

    /**
     * @test where
     */    
    public function testWhere() {
        $this->assertFalse(where('field', 'data', 'data2'));
    } 

    /**
     * @test groupBy
     */    
    public function testGroupBy() {
        $this->assertFalse(groupBy(''));
        $this->assertNotNull(groupBy('field'));
    } 
 
    /**
     * @test having
     */   
    public function testHaving() {
        $this->assertFalse(having('field', 'data', 'data2'));
    }

    /**
     * @test orderBy
     */    
    public function testOrderBy() {
        $this->assertFalse(orderBy('', 'data'));
        $this->assertNotNull(orderBy('field', 'data'));
    } 

    /**
     * @test insert
     */    
    public function testInsert() {
        $this->assertFalse(insert('field', ['data' => 'data2']));
    } 

    /**
     * @test update
     */    
    public function testUpdate() {
        $this->assertFalse(update('field', 'data', 'data2'));
    } 

    /**
     * @test delete
     */    
    public function testDeleting() {
        $this->assertFalse(deleting('field', 'data', 'data2'));
    } 

    /**
     * @test replace
     */        
    public function testReplace() {
        $this->assertFalse(replace('field', ['data' => 'data2']));
    }  
} //
