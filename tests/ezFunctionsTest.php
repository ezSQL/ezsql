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
     * @test eq 
     */
    public function testeq()
    {
        $this->assertInternalType('array',eq('field', 'data'));
        $this->assertArraySubset([1 => EQ], eq('field', 'data'));
    }

    /**
     * @test neq
     */
    public function testneq()
    {
        $this->assertInternalType('array',neq('field', 'data'));
        $this->assertArraySubset([3 => _AND], neq('field', 'data', _AND));
    }

    /**
     * @test ne
     */
    public function testne()
    {
        $this->assertInternalType('array',ne('field', 'data'));
        $this->assertArraySubset([4 => 'extra'], ne('field', 'data', _AND, 'extra'));
    }
    
    /**
     * @test lt
     */
    public function testlt()
    {
        $this->assertInternalType('array',lt('field', 'data'));
        $this->assertArraySubset([2 => 'data'], lt('field', 'data'));
    }

    /**
     * @test lte
     */
    public function testlte()
    {
        $this->assertInternalType('array',lte('field', 'data'));
        $this->assertArraySubset([0 => 'field'], lte('field', 'data'));
    }

    /**
     * @test gt
     */
    public function testgt()
    {
        $this->assertInternalType('array',gt('field', 'data'));
        $this->assertArraySubset([0 => 'field'], gt('field', 'data'));
    }

    /**
     * @test gte
     */
    public function testgte()
    {
        $this->assertInternalType('array',gte('field', 'data'));
        $this->assertArraySubset([0 => 'field'], gte('field', 'data'));
    }

    /**
     * @test isNull
     */
    public function testisNull()
    {
        $this->assertInternalType('array',isNull('field'));
        $this->assertArraySubset([2 => 'null'], isNull('field'));
    }

    /**
     * @test isNotNull
     */
    public function testisNotNull()
    {
        $this->assertInternalType('array',isNotNull('field'));
        $this->assertArraySubset([2 => 'null'], isNotNull('field'));
    }

    /**
     * @test like
     */
    public function testlike()
    {
        $this->assertInternalType('array',like('field', 'data'));
        $this->assertArraySubset([2 => 'data'], like('field', 'data'));
    }

    /**
     * @test notLike
     */
    public function testnotLike()
    {
        $this->assertInternalType('array',notLike('field', 'data'));
        $this->assertArraySubset([2 => 'data'], notLike('field', 'data'));
    }

    /**
     * @test in
     */
    public function testin()
    {
        $this->assertInternalType('array',in('field', 'data'));
        $this->assertArraySubset([8 => 'data6'], in('field', 'data', 'data1', 'data2', 'data3', 'data4', 'data5', 'data6'));
    }

    /**
     * @test notIn
     */
    public function testnotIn()
    {
        $this->assertInternalType('array',notIn('field', 'data'));
        $this->assertArraySubset([5 => 'data3'], notIn('field', 'data', 'data1', 'data2', 'data3', 'data4', 'data5', 'data6'));
    }

    /**
     * @test between
     */
    public function testbetween()
    {
        $this->assertInternalType('array',between('field', 'data', 'data2'));
        $this->assertArraySubset([1 => _BETWEEN], between('field', 'data', 'data2'));
    }

    /**
     * @test notBetween
     */
    public function testNotBetween()
    {
        $this->assertInternalType('array',notBetween('field', 'data', 'data2'));
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
    public function testselect_into() {
        $this->assertFalse(select_into('field', 'data', 'data2'));
    } 

    /**
     * @test insert_select
     */    
    public function testinsert_select() {
        $this->assertFalse(insert_select('field', 'data', 'data2'));
    }     

    /**
     * @test create_select
     */    
    public function testcreate_select() {
        $this->assertFalse(create_select('field', 'data', 'data2'));
    }  

    /**
     * @test where
     */    
    public function testwhere() {
        $this->assertFalse(where('field', 'data', 'data2'));
    } 

    /**
     * @test groupBy
     */    
    public function testgroupBy() {
        $this->assertFalse(groupBy(''));
        $this->assertNotNull(groupBy('field'));
    } 
 
    /**
     * @test having
     */   
    public function testhaving() {
        $this->assertFalse(having('field', 'data', 'data2'));
    }

    /**
     * @test orderBy
     */    
    public function testorderBy() {
        $this->assertFalse(orderBy('', 'data'));
        $this->assertNotNull(orderBy('field', 'data'));
    } 

    /**
     * @test insert
     */    
    public function testinsert() {
        $this->assertFalse(insert('field', ['data' => 'data2']));
    } 

    /**
     * @test update
     */    
    public function testupdate() {
        $this->assertFalse(update('field', 'data', 'data2'));
    } 

    /**
     * @test delete
     */    
    public function testdelete() {
        $this->assertFalse(delete('field', 'data', 'data2'));
    } 

    /**
     * @test replace
     */        
    public function testreplace() {
        $this->assertFalse(replace('field', ['data' => 'data2']));
    }  
} //
