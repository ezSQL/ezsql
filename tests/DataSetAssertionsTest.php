<?php
require 'vendor/autoload.php';
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

class ezsqlCoreTestCase extends TestCase
{
    use TestCaseTrait;

    /**
     * @return PHPUnit\DbUnit\Database\Connection
     */
    public function getConnection()
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec("CREATE TABLE users (id PRIMARY KEY, name VARCHAR(50), email VARCHAR(50))");
        $pdo->exec("INSERT INTO users (id, name, email) VALUES (20, 'Bas', 'aa@me')");
        return $this->createDefaultDBConnection($pdo, ':memory:');
    }

    /**
     * @return PHPUnit\DbUnit\DataSet\IDataSet
     */
    public function getDataSet() {
        return $this->createMySQLXMLDataSet(dirname(__FILE__).'\testingdb.xml');
    }

    
    public function testCreateDataSetAssertion()
    {
    $ds = new PHPUnit\DbUnit\DataSet\QueryDataSet($this->getConnection());
    $ds->addTable('users', 'SELECT id, name FROM users');
    $ds->addTable('usersy', 'SELECT id, name, email FROM users');
    $ds->addTable('usersy5', 'SELECT id, name, email FROM users');

        $dataSet = $this->getConnection()->createDataSet(['users']);
        $expectedDataSet = $this->getDataSet();
        $this->assertDataSetsEqual($expectedDataSet, $ds);
    }
}

