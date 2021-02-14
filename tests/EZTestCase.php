<?php

namespace ezsql\Tests;

abstract class EZTestCase extends \PHPUnit\Framework\TestCase
{
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
     * constant string database port
     */
    const TEST_DB_PORT = '5432';

    /**
     * constant string path and file name of the SQLite test database
     */
    const TEST_SQLITE_DB = 'ez_test.sqlite3';
    const TEST_SQLITE_DB_DIR = './tests/sqlite/';

    protected $errors;

    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext = null)
    {
        $this->errors[] = compact("errno", "errstr", "errfile", "errline", "errcontext");
    }

    public function assertError($errstr, $errno)
    {
        foreach ($this->errors as $error) {
            if ($error["errstr"] === $errstr && $error["errno"] === $errno) {
                return;
            }
        }

        $this->fail();
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
