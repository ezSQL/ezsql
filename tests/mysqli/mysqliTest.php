<?php

namespace ezsql\Tests;

use ezsql\ezSchema;
use ezsql\Database;
use ezsql\Database\ez_mysqli;
use ezsql\Tests\EZTestCase;

class ez_mysqliTest extends EZTestCase 
{
    /**
     * @var ez_mysqli
     */
    protected $object;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
	{
        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped(
              'The MySQLi extension is not available.'
            );
        }

        $this->object = Database::initialize('mysqli', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->object->setPrepare();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() 
    {
        if ($this->object->isConnected()) {
            $this->object->select(self::TEST_DB_NAME);
            $this->assertEquals($this->object->query('DROP TABLE IF EXISTS unit_test'), 0);
        }
        $this->object = null;
    }
       
    /**
     * @covers ezsql\Database\ez_mysqli::quick_connect
     */
    public function testQuick_connect() 
    {
        $result = $this->object->quick_connect();

        $this->assertTrue($result);
    }

    /**
     * @covers ezsql\Database\ez_mysqli::quick_connect
     */
    public function testQuick_connect2() 
    {
        $result = $this->object->quick_connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME, self::TEST_DB_CHARSET);

        $this->assertTrue($result);
    }

    /**
     * @covers ezsql\Database\ez_mysqli::connect
     */
    public function testConnect() 
    {        
        $this->errors = array();
        set_error_handler(array($this, 'errorHandler')); 
         
        $this->assertFalse($this->object->connect('no',''));  
        $this->assertFalse($this->object->connect('self::TEST_DB_USER', 'self::TEST_DB_PASSWORD',' self::TEST_DB_NAME', 'self::TEST_DB_CHARSET'));  
        $result = $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);

        $this->assertTrue($result);
    }

    /**
     * @covers ezsql\Database\ez_mysqli::select
     */
    public function testSelect() 
    {
        $this->object->connect();
        $this->assertTrue($this->object->isConnected());

        $result = $this->object->select(self::TEST_DB_NAME);

        $this->assertTrue($result);

        $this->errors = array();
        set_error_handler(array($this, 'errorHandler')); 
        $this->assertTrue($this->object->select(''));
        $this->object->disconnect();
        $this->assertFalse($this->object->select('notest'));
        $this->object->connect();
        $this->assertFalse($this->object->select('notest'));
        $this->assertTrue($this->object->select(self::TEST_DB_NAME));        
    } // testSelect

    /**
     * @covers ezsql\Database\ez_mysqli::escape
     */
    public function testEscape() 
    {
        $this->object->connect();
        $result = $this->object->escape("This is'nt escaped.");

        $this->assertEquals("This is\\'nt escaped.", $result);
    } // testEscape

    /**
     * @covers ezsql\Database\ez_mysqli::sysDate
     */
    public function testSysDate() 
    {
        $this->assertEquals('NOW()', $this->object->sysDate());
    } 

    /**
     * @covers ezsql\Database\ez_mysqli::query
     */
    public function testQueryInsert() 
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);

        $this->object->select(self::TEST_DB_NAME);

        $this->assertEquals($this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'), 0);
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(1, \'test 1\')'), 1);
        
        $this->object->dbh = null;
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(2, \'test 2\')'),1);
        $this->object->disconnect();
        $this->assertNull($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(3, \'test 3\')'));        
    }

    /**
     * @covers ezsql\Database\ez_mysqli::query
     */
    public function testQuerySelect() 
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);

        $this->object->select(self::TEST_DB_NAME);
        
        $this->assertEquals($this->object->query('DROP TABLE IF EXISTS unit_test'), 0); 
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
    }

    /**
     * @covers ezsql\ezsqlModel::get_results
     */
    public function testGet_results() 
    {         
        $this->assertEquals($this->object->query('CREATE TABLE unit_test(id integer, test_key varchar(50), PRIMARY KEY (ID))'), 0);

        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(1, \'test 1\')'), 1);
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(2, \'test 2\')'), 1);
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(3, \'test 3\')'), 1);

        $this->object->query('SELECT * FROM unit_test');
        $result = $this->object->get_results('SELECT * FROM unit_test', _JSON);
                
        $this->assertEquals('[{"id":"1","test_key":"test 1"},{"id":"2","test_key":"test 2"},{"id":"3","test_key":"test 3"}]', $result);

    }

    /**
     * @covers ezsql\Database\ez_mysqli::getDBHost
     */
    public function testGetDBHost() 
    {
        $this->assertEquals(self::TEST_DB_HOST, $this->object->getDBHost());
    }

    /**
     * @covers ezsql\Database\ez_mysqli::getCharset
     */
    public function testGetCharset() 
    {
        $this->assertEquals(self::TEST_DB_CHARSET, $this->object->getCharset());
    } // testGetCharset
    
    /**
     * @covers ezsql\ezsqlModel::get_set
     */
    public function testGet_set()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $this->object->select(self::TEST_DB_NAME);
        $this->assertContains('NOW()',$this->object->get_set(
            array('test_unit'=>'NULL',
            'test_unit2'=>'NOW()',
            'test_unit3'=>'true',
            'test_unit4'=>'false')));   
    }

    /**
     * @covers ezsql\Database\ez_mysqli::disconnect
     */
    public function testDisconnect() 
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $this->object->select(self::TEST_DB_NAME);
        $this->object->disconnect();
        $this->assertFalse($this->object->isConnected());
    } // testDisconnect

    /**
     * @covers ezsql\Database\ez_mysqli::getInsertId
     */
    public function testGetInsertId() 
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);

        $this->object->select(self::TEST_DB_NAME);

        $this->assertEquals($this->object->query('CREATE TABLE unit_test(id int(11) NOT NULL AUTO_INCREMENT, test_key varchar(50), PRIMARY KEY (ID))ENGINE=MyISAM  DEFAULT CHARSET=utf8'), 0);
        $this->assertEquals($this->object->query('INSERT INTO unit_test(id, test_key) VALUES(1, \'test 1\')'), 1);

        $this->assertEquals(1, $this->object->getInsertId($this->object->dbh));
    } // testInsertId
 
    /**
     * @covers ezsql\ezQuery::create
     */
    public function testCreate()
    {
        $object = Database::initialize('mysqli', [self::TEST_DB_USER, self::TEST_DB_PASSWORD, self::TEST_DB_NAME]);
        $this->assertEquals($this->object, $object);
        $object->connect();
        $object->select(self::TEST_DB_NAME);
             
        $this->assertEquals($object->create('create_test',
            \column('id', INTR, 11, \AUTO),
            \column('create_key', VARCHAR, 50),
            \primary('id_pk', 'id')), 
        0);

        $object->setPrepare(false);
        $this->assertEquals(1, $object->insert('create_test',
            ['create_key' => 'test 2']));
        $this->setPrepare();
    }

    /**
     * @covers ezsql\ezQuery::drop
     */
    public function testDrop()
    {             
        $this->assertEquals($this->object->drop('create_test'), 0);
    }
   
    /**
     * @covers ezsql\ezQuery::insert
     */
    public function testInsert()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $this->object->select(self::TEST_DB_NAME);
        $this->object->query('CREATE TABLE unit_test(id int(11) NOT NULL AUTO_INCREMENT, test_key varchar(50), PRIMARY KEY (ID))ENGINE=MyISAM  DEFAULT CHARSET=utf8');
        $this->assertEquals($this->object->insert('unit_test', array('id'=>2, 'test_key'=>'test 2' )), 2);
    }
        
    /**
     * @covers ezsql\ezQuery::replace
     */
    public function testReplace()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $this->object->select(self::TEST_DB_NAME);
        $this->object->query('CREATE TABLE unit_test(id int(11) NOT NULL AUTO_INCREMENT, test_key varchar(50), PRIMARY KEY (ID))ENGINE=MyISAM  DEFAULT CHARSET=utf8');
        $this->object->insert('unit_test', array('id'=>2, 'test_key'=>'test 2' ));
        $this->assertEquals($this->object->replace('unit_test', array('id'=>2, 'test_key'=>'test 3' )), 2);
    }
    
    /**
     * @covers ezsql\ezQuery::update
     */
    public function testUpdate()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $this->object->select(self::TEST_DB_NAME);  
        $this->object->query('CREATE TABLE unit_test(id int(11) NOT NULL AUTO_INCREMENT, test_key varchar(50), PRIMARY KEY (ID))ENGINE=MyISAM  DEFAULT CHARSET=utf8');
        $this->object->insert('unit_test', array('id'=>11, 'test_key'=>'testUpdate() 11' ));
        $this->object->insert('unit_test', array('id'=>12, 'test_key'=>'testUpdate() 12' ));
        $this->object->insert('unit_test', array('id'=>13, 'test_key'=>'testUpdate() 13' ));
        $unit_test['test_key'] = 'testing testUpdate()';
        $where="id  =  11";
        $this->assertEquals($this->object->update('unit_test', $unit_test, $where), 1);
        $this->assertEquals($this->object->update('unit_test', $unit_test, 	['test_key',EQ,'testUpdate() 13', 'and'], ['id','=', 13]), 1);
        $this->assertEquals($this->object->update('unit_test', $unit_test, "id = 14"), 0);
        $this->assertEquals($this->object->update('unit_test', $unit_test, "test_key  =  testUpdate() 12  and", "id  =  12"), 1);
        $this->assertEquals($this->object->query('DROP TABLE IF EXISTS unit_test'), 0);
    }
    
    /**
     * @covers ezsql\ezQuery::delete
     */
    public function testDelete()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $this->object->select(self::TEST_DB_NAME);
        $this->object->query('CREATE TABLE unit_test(id int(11) NOT NULL AUTO_INCREMENT, test_key varchar(50), PRIMARY KEY (ID))ENGINE=MyISAM  DEFAULT CHARSET=utf8');
        $unit_test['id'] = 1;
        $unit_test['test_key'] = 'testDelete() 11';
        $this->object->insert('unit_test', $unit_test );
        $unit_test['id'] = 2;
        $unit_test['test_key'] = 'testDelete() 12';
        $this->object->insert('unit_test', $unit_test );
        $unit_test['id'] = 3;
        $unit_test['test_key'] = 'testDelete() 13';
        $this->object->insert('unit_test', $unit_test );
        $where=1;
        $this->assertEquals($this->object->delete('unit_test', ['id','=',1]), 1);
        $this->assertEquals($this->object->delete('unit_test', ['test_key','=',$unit_test['test_key'],'and'],['id','=',3]), 1);
        $this->assertEquals($this->object->delete('unit_test', ['test_key','=',$where]), 0);
        $where="id  =  2";
        $this->assertEquals($this->object->delete('unit_test', $where), 1);
    }  
       
    /**
     * @covers ezsql\ezQuery::selecting
     */
    public function testSelecting()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $this->object->select(self::TEST_DB_NAME);
        $this->object->query('CREATE TABLE unit_test(id int(11) NOT NULL AUTO_INCREMENT, test_key varchar(50), PRIMARY KEY (ID))ENGINE=MyISAM  DEFAULT CHARSET=utf8');
        $this->object->insert('unit_test', array('id'=>1, 'test_key'=>'testing 1' ));
        $this->object->insert('unit_test', array('id'=>2, 'test_key'=>'testing 2' ));
        $this->object->insert('unit_test', array('id'=>3, 'test_key'=>'testing 3' ));
        
        $result = $this->object->selecting('unit_test');
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            ++$i;
        }
        
        $where=['test_key','=','testing 2'];
        $result = $this->object->selecting('unit_test', 'id', $where);
        foreach ($result as $row) {
            $this->assertEquals(2, $row->id);
        }
        
        $result = $this->object->selecting('unit_test', 'test_key', ['id','=',3 ]);
        foreach ($result as $row) {
            $this->assertEquals('testing 3', $row->test_key);
        }
        
        $result = $this->object->selecting('unit_test', array ('test_key'), "id  =  1");
        foreach ($result as $row) {
            $this->assertEquals('testing 1', $row->test_key);
        }
    }    
          
    /**
     * @covers ezsql\ezQuery::create_select
     */
    public function testCreate_select()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $this->object->select(self::TEST_DB_NAME);
        $this->object->query('CREATE TABLE unit_test(id int(11) NOT NULL AUTO_INCREMENT, test_key varchar(50), PRIMARY KEY (ID))ENGINE=MyISAM  DEFAULT CHARSET=utf8');
        $this->object->insert('unit_test', array('id'=>'1', 'test_key'=>'testing 1' ));
        $this->object->insert('unit_test', array('id'=>'2', 'test_key'=>'testing 2' ));
        $this->object->insert('unit_test', array('id'=>'3', 'test_key'=>'testing 3' ));
        
		$this->assertEquals($this->object->create_select('new_new_test','*','unit_test'),0);
		$result = $this->object->selecting('new_new_test');
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            ++$i;
        }
        $this->assertEquals($this->object->query('DROP TABLE IF EXISTS new_new_test'), 0);    
    }    
              
    /**
     * @covers ezsql\ezQuery::insert_select
     */
    public function testInsert_select()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $this->object->select(self::TEST_DB_NAME);
        $this->object->query('CREATE TABLE unit_test(id int(11) NOT NULL AUTO_INCREMENT, test_key varchar(50), PRIMARY KEY (ID))ENGINE=MyISAM  DEFAULT CHARSET=utf8');
        $this->object->insert('unit_test', array('id'=>'1', 'test_key'=>'testing 1' ));
        $this->object->insert('unit_test', array('id'=>'2', 'test_key'=>'testing 2' ));
        $this->object->insert('unit_test', array('id'=>'3', 'test_key'=>'testing 3' ));
        
        $this->assertEquals($this->object->query('DROP TABLE IF EXISTS new_select_test'), 0);
        $this->object->query('CREATE TABLE new_select_test(id int(11) NOT NULL AUTO_INCREMENT, test_key varchar(50), PRIMARY KEY (ID))ENGINE=MyISAM  DEFAULT CHARSET=utf8');
		
		$this->assertEquals($this->object->insert_select('new_select_test','*','unit_test'),3);
        setInstance('mySQLi');
		$result = select('new_select_test');
        $i = 1;
        foreach ($result as $row) {
            $this->assertEquals($i, $row->id);
            $this->assertEquals('testing ' . $i, $row->test_key);
            ++$i;
        }
        $this->assertEquals($this->object->query('DROP TABLE IF EXISTS new_select_test'), 0);
    }    
	
    /**
     * @covers ezsql\ezQuery::where
     */
    public function testWhere()
    {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $this->object->select(self::TEST_DB_NAME);
        setInstance('mySQLi');       
        $this->object->setPrepare(false);
        $expect = where(
            between('where_test','testing 1','testing 2','bad'),
			like('test_null','null')
			);

        $this->assertContains('WHERE where_test BETWEEN \'testing 1\' AND \'testing 2\' AND test_null IS NULL',$expect);
        $this->assertFalse(where(
            array('where_test','bad','testing 1','or'),
			array('test_null','like','null')
			));
        $expect = $this->object->where(
            array('where_test',_IN,'testing 1','testing 2','testing 3','testing 4','testing 5')
			);
        $this->assertContains('WHERE',$expect);
        $this->assertContains('IN',$expect);
        $this->assertContains('(',$expect);
        $this->assertContains('testing 1',$expect);
        $this->assertContains('testing 4\',',$expect);
        $this->assertContains(')',$expect);
        $expect = $this->object->where("where_test  in  testing 1  testing 2  testing 3  testing 4  testing 5");
        $this->assertContains('WHERE',$expect);
        $this->assertContains('IN',$expect);
        $this->assertContains('(',$expect);
        $this->assertContains('testing 2\'',$expect);
        $this->assertContains('testing 5',$expect);
        $this->assertContains(')',$expect);
        $this->assertFalse($this->object->where(
            array('where_test','=','testing 1','or'),
			array('test_like','LIKE',':bad')
			));
        $this->assertContains('_good',$this->object->where(
            array('where_test','=','testing 1','or'),
			array('test_like',_LIKE,'_good')
			));                   
        $this->object->setPrepare(true);
        $expect = where(
            between('where_test','testing 1','testing 2','bad'),
			like('test_null','null')
			);

        $this->assertContains('WHERE where_test BETWEEN '._TAG.' AND '._TAG.' AND test_null IS NULL',$expect);
        setInstance();       
    } 
    
    /**
     * @covers ezsql\Database\ez_mysqli::query_prepared
     */
    public function testQuery_prepared() {
        $this->object->connect(self::TEST_DB_USER, self::TEST_DB_PASSWORD);
        $this->object->select(self::TEST_DB_NAME);       
        $this->assertEquals($this->object->query('CREATE TABLE unit_test(id int(11) NOT NULL AUTO_INCREMENT, test_key varchar(50), PRIMARY KEY (ID))ENGINE=MyISAM  DEFAULT CHARSET=utf8'), 0);
        $result = $this->object->query_prepared('INSERT INTO unit_test(id, test_key) VALUES(1, ?)', ['test 1']);
        $this->assertEquals(1, $result);
    } // testQuery_prepared
       
    /**
     * @covers ezsql\Database\ez_mysqli::__construct
     */
    public function test__Construct() {
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/[Missing configuration details]/');
        $this->assertNull(new ez_mysqli);  
    } 
} // ez_mysqliTest