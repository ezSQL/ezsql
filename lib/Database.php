<?php 

declare(strict_types=1);

namespace ezsql;

use ezsql\Configuration;
//use ezsql\ezResultset;
use ezsql\DInjector;
use const ezsql\Constants\VENDOR as VENDOR;

class Database
{
    /**
     * Timestamp for benchmark.
     *
     * @var float
     */
    private $_ts = null;

    /**
     * Database configuration setting 
     * @var Configuration instance
     */
    private $database;

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    /**
     * Initialize and connect a sql database driver
     *
     * @param class $settings with sql driver and connection parameters
     */    
    public static function initialize(Configuration $settings)
    { 
        if  (empty($settings) || (!$settings instanceof Configuration)) {
            throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
        } else {
            self::$_ts = microtime();
            self::$database = $settings;
            $key = self::$database->getDriver();
            $value = VENDOR[$key];

            if (empty($GLOBALS['db_'.$key])) {    
                $di = new DInjector();
                $GLOBALS['db_'.$key] = $di->autoWire( $value, self::$database); 
            }

            return $GLOBALS['db_'.$key];
        }
    }

    /**
     * Print-out a memory used benchmark.
     *
     * @return float Time elapsed.
     */
    public function benchmark()
    {
        return [
            'start'  => $this->_ts,
            'elapse' => microtime() - $this->_ts,
            'memory' => memory_get_usage(true),
        ];
    }
}