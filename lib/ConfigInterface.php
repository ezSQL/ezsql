<?php
declare(strict_types=1);

namespace ezsql;

interface ConfigInterface
{
    /**
     * initializing/connection settings for vendors SQL database class
     *     
     * @param string $driver - The vendor's SQL database driver name
     * @param string|array $args - of the following:
     * 
     * @param string $path  /args[0] - The path to open an SQLite database
     * @param string $dsn   /args[0] - The PDO DSN connection parameter string
     * 
     * @param string $user  /args[0][1] - The database user name
     * @param string $password  /args[1][2] - The database users password
     * 
     * @param string $name  /args[1][2] - The name of the database
     * @param string $host  /args[3] - The host name or IP address of the database server,
     *                                   Default is localhost
     * @param string $charset   /args[4] - The database charset, Default is empty string
     * 
     * @param array $options    /args[3] - Array for setting connection options as MySQL
     * @param boolean $isFile   /args[4] - File based databases like SQLite don't need user
     *                                   and password, work with path in the dsn parameter
     * @param string $port  /args[4] - The PostgreSQL database TCP/IP port, Default is 5432
     */
    public static function initialize(string $driver = '', $arguments = null);
}