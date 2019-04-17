<?php

namespace ezsql\Tests;

use ezsql\ezQuery;
use ezsql\Tests\EZTestCase;

class ezQueryTest extends EZTestCase 
{
	
    /**
     * @var ezQuery
     */
    protected $object;
	
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
	{
        $this->object = new ezQuery();             
    } // setUp

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->object = null;
    } // tearDown
 
    /**
      * @covers ezsql\ezQuery::clean
     */
    public function testClean()
    {
        $this->assertEquals("' help", $this->object->clean("<?php echo 'foo' >' help</php?>"));
    } 

    /**
     * @covers ezsql\ezQuery::having
     */
    public function testHaving()
    {
        $this->assertFalse($this->object->having(''));
        $this->assertEmpty($this->object->having()); 

        $expect = $this->object->having("other_test  in  testing 1  testing 2  testing 3  testing 4  testing 5");

        $this->assertContains('HAVING', $expect);
    }

    /**
     * @covers ezsql\ezQuery::where
     * @covers ezsql\ezQuery::conditionIs
     * @covers ezsql\ezQuery::conditionBetween
     * @covers ezsql\ezQuery::conditions
     * @covers ezsql\ezQuery::conditionIn
     * @covers ezsql\ezQuery::isPrepareOn
     */
    public function testWhere()
    {
        $this->assertFalse($this->object->where(''));
        $this->assertEmpty($this->object->where()); 

        $expect = $this->object->where("where_test  in  testing 1  testing 2  testing 3  testing 4  testing 5");

        $this->assertContains('WHERE', $expect);
        $this->assertContains('IN', $expect);
        $this->assertContains('(', $expect);
        $this->assertContains('testing 2\'', $expect);
        $this->assertContains('testing 5', $expect);
        $this->assertContains(')', $expect);
        
        $this->assertContains('AND', $this->object->where(
            array('where_test', '=', 'testing 1'),
			array('test_like', _LIKE, '_good'))
        );

        $this->object->prepareOn();  
        $this->assertContains('__ez__', $this->object->where( eq('where_test', 'testing 1') ));        
        $this->assertFalse($this->object->where( like('where_test', 'fail') ));        
    }

    /**
     * @covers ezsql\ezQuery::prepareOn
     * @covers ezsql\ezQuery::where
     * @covers ezsql\ezQuery::conditionIs
     * @covers ezsql\ezQuery::conditionBetween
     * @covers ezsql\ezQuery::conditions
     * @covers ezsql\ezQuery::conditionIn
     */
    public function testPrepareOn()
    {
        $this->object->prepareOn();            
        $expect = $this->object->where(
            ['where_test', _IN, 'testing 1', 'testing 2', 'testing 3', 'testing 4', 'testing 5']
        );
        
        $this->assertEquals(5, preg_match_all('/__ez__/', $expect));
    }
    
    /**
     * @covers ezsql\ezQuery::prepareOff
     */
    public function testPrepareOff()
    {
        $this->object->prepareOff();  

        $this->assertFalse($this->object->where(
            array('where_test', '=', 'testing 1', 'or'),
			array('test_like', 'LIKE', ':bad'))
        );            
    }

    /**
     * @covers ezsql\ezQuery::addPrepare
     * @covers ezsql\ezQuery::isPrepareOn
     * @covers ezsql\ezQuery::prepareValues
     */
    public function testAddPrepare()
    {
        $this->object->prepareOn();            
        $expect = $this->object->where( 
            eq('where_test', 'testing 1'), 
            neq('some_key', 'other', _OR),
            like('other_key', '%any')
        );

        $this->assertEquals(3, preg_match_all('/__ez__/', $expect));     
    }     

    /**
     * @covers ezsql\ezQuery::delete
     */
    public function testDelete()
    {
        $this->assertFalse($this->object->delete(''));
        $this->assertFalse($this->object->delete('test_unit_delete',array('good','bad')));
    }
       
    /**
     * @covers ezsql\ezQuery::selecting
     * @covers ezsql\ezQuery::clearPrepare
     */
    public function testSelecting()
    {
        $this->assertFalse($this->object->selecting('',''));
        //$this->expectException(\Error::class);
        //$this->expectExceptionMessageRegExp('/[Call to undefined method ezsql\ezQuery::get_results()]/');
        $this->assertNotNull($this->object->selecting('table','columns','WHERE','GROUP BY','HAVING','ORDER BY','LIMIT'));
    }
    
    /**
     * @covers ezsql\ezQuery::create_select
     */
    public function testCreate_select()
    {
        $this->assertFalse($this->object->create_select('','',''));
    }
    
    /**
     * @covers ezsql\ezQuery::insert_select
     */
    public function testInsert_select()
    {
        $this->assertFalse($this->object->insert_select('','',''));
    }
    
    /**
     * @covers ezsql\ezQuery::insert
     */
    public function testInsert()
    {
        $this->assertFalse($this->object->insert('',''));
    }
    
    /**
     * @covers ezsql\ezQuery::update
     */
    public function testUpdate()
    {
        $this->assertFalse($this->object->update('',''));
        $this->assertFalse($this->object->update('test_unit_delete',array('test_unit_update'=>'date()'),''));
    }
	
    /**
     * @covers ezsql\ezQuery::replace
     */
    public function testReplace()
    {
        $this->assertFalse($this->object->replace('',''));
    }
    
    /**
     * @covers ezsql\ezQuery::__construct
     */
    public function test__Construct() {         
        $ezQuery = $this->getMockBuilder(ezQuery::class)
        ->setMethods(null)
        ->disableOriginalConstructor()
        ->getMock();
        
        $this->assertNull($ezQuery->__construct());  
    }
} //
