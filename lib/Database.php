<?php 

declare(strict_types=1);

namespace ezsql;

use ezsql\DInjector;

class Database
{
    /**
     * Timestamp for benchmark.
     *
     * @var float
     */
    private static $_ts = null;

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    /**
     * Initialize and connect a vendor database.
     * 
     * @param mixed $vendor - SQL driver
     * @param mixed $setting - SQL connection parameters
     */    
    public static function initialize(string $vendor = null, $setting = null)
    { 
        if  (empty($vendor) || empty($setting)) {
            throw new \Exception(\MISSING_CONFIGURATION);
        } else {
            self::$_ts = \microtime(true);
            $key = $vendor;
            $value = \VENDOR[$key];

            if (empty($GLOBALS['db_'.$key])) {
                $di = new DInjector();
                $di->set($key, $value);                
                $di->set('ezsql\ConfigInterface', 'ezsql\Config');
                $GLOBALS['db_'.$key] = $di->get($key, ['driver' => $key, 'arguments' => $setting]); 
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
            'elapse' => \microtime(true) - self::$_ts,
            'memory' => \memory_get_usage(true),
        ];
    }
}