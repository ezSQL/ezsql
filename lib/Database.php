<?php 

declare(strict_types=1);

namespace ezsql;

use ezsql\Configuration;
use ezsql\DInjector;

class Database
{
    /**
     * Timestamp for benchmark.
     *
     * @var float
     */
    private static $_ts = null;

    /**
     * Database configuration setting 
     * @var Configuration instance
     */
    private static $database;

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    /**
     * Initialize and connect a vendor database.
     * 
     * @param object $settings - Has SQL driver connection parameters
     */    
    public static function initialize(string $vendor, $settings)
    { 
        if  (empty($settings) || empty($vendor)) {
            throw new \Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
        } else {
            self::$_ts = \microtime();
            self::$database = new Configuration($vendor, $settings);
            $key = self::$database->getDriver();
            $value = \VENDOR[$key];

            if (empty($GLOBALS['db_'.$key])) {    
                $di = new DInjector();
                $GLOBALS['db_'.$key] = $di->autoWire($value, self::$database); 
            }

            return $GLOBALS['db_'.$key];
        }
    }

    /**
     * Print-out a memory used benchmark.
     *
     * @return array|float time elapsed, memory usage.
     */
    public static function benchmark()
    {
        return [
            'start'  => self::$_ts,
            'elapse' => \microtime() - self::$_ts,
            'memory' => \memory_get_usage(true),
        ];
    }

    public static function settings()
    {
        return self::$database;
    }
}