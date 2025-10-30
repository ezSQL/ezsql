<?php

declare(strict_types=1);

namespace ezsql;

use ezsql\Db;
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
     * @var ezQueryInterface[]
     */
    private static $instances = [];

    // @codeCoverageIgnoreStart
    private function __construct()
    {
    }
    private function __clone()
    {
    }
    public function __wakeup()
    {
    }
    // @codeCoverageIgnoreEnd

    /**
     * Initialize and connect a vendor database.
     *
     * @param string $vendor SQL driver
     * @param array $setting SQL connection parameters, in the following:
     *```js
     * [
     *  user,  // The database user name.
     *  password, // The database users password.
     *  database, // The name of the database.
     *  host,   // The host name or IP address of the database server. Default is localhost
     *  port    // The  database TCP/IP port. Default is: 5432 - PostgreSQL, 3306 - MySQL
     * ]
     *```
     *  for: **mysqli** - (`username`, `password`, `database`, `host`, `port`, `charset`)
     * - `charset` // The database charset,
     *      Default is empty string
     *
     *  for: **postgresql** - (`username`, `password`, `database`, `host`, `port`)
     *
     *  for: **sqlserver** - (`username`, `password`, `database`, `host`, `convertMysqlToMssqlQuery`)
     * - `convertMysqlToMssqlQuery` // convert Queries in MySql syntax to MS-SQL syntax
     *      Default is false
     *
     *  for: **pdo** - (`dsn`, `username`, `password`, `options`, `isFile`?)
     * - `dsn`  // The PDO DSN connection parameter string
     * - `options` // Array for setting connection options as MySQL
     * - `isFile` // File based databases like SQLite don't need
     *      user and password, they work with path in the dsn parameter
     *      Default is false
     *
     *  for: **sqlite3** - (`filePath`, `database`)
     * - `filePath` // The path to open an SQLite database
     *
     * @param string $tag Store the instance for later use
     * @return Database\ez_pdo|Database\ez_pgsql|Database\ez_sqlsrv|Database\ez_sqlite3|Database\ez_mysqli
     */
    public static function initialize(?string $vendor = null, ?array $setting = null, ?string $tag = null): ezQueryInterface
    {
        if (isset(self::$instances[$vendor]) && empty($setting) && empty($tag))
            return self::$instances[$vendor];

        if (empty($vendor) || empty($setting)) {
            throw new \Exception(\MISSING_CONFIGURATION);
        } else {
            self::$_ts = \microtime(true);
            $key = $vendor;
            $value = \VENDOR[$key];

            if (!Db::has('ez' . $key) || !empty($tag)) {
                $di = new DInjector();
                $di->set($key, $value);
                $di->set('ezsql\ConfigInterface', 'ezsql\Config');
                $instance = $di->get($key, ['driver' => $key, 'arguments' => $setting]);
                if (!empty($tag)) {
                    self::$instances[$tag] = $instance;
                    return $instance;
                }
            }

            $db = Db::get('ez' . $key);
            Db::set('global', $db);
            return $db;
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
