<?php

namespace ezsql;

/**
 * @method void setDriver($args);
 * Database Sql driver name
 * @method void setDsn($args);
 * The PDO connection parameter string, database server in the DSN parameters
 * @method void setUser($args);
 * Database user name
 * @method void setPassword($args);
 * Database password for the given user
 * @method void setName($args);
 * Database name
 * @method void setHost($args);
 * Host name or IP address
 * @method void setPort($args);
 * TCP/IP port of PostgreSQL/MySQL
 * @method void setCharset($args);
 * Database charset
 * @method void setOptions($args);
 * The PDO array for connection options, MySQL connection charset, for example
 * @method void setIsFile($args);
 * Check PDO for whether it is a file based database connection, for example to a SQLite
 * database file, or not
 * @method void setToMssql($args);
 * If we want to convert Queries in MySql syntax to MS-SQL syntax.
 * Yes, there are some differences in query syntax.
 * @method void setToMysql($args);
 * If we want to convert Queries in MySql syntax to MS-SQL syntax.
 * Yes, there are some differences in query syntax.
 * @method void setPath($args);
 * The path to open an SQLite database
 *
 * @method string getDriver();
 * Database Sql driver name
 * @method string getDsn();
 * The PDO connection parameter string, database server in the DSN parameters
 * @method string getUser();
 * Database user name
 * @method string getPassword()
 * Database password for the given user
 * @method string getName();
 * Database name
 * @method string getHost();
 * Host name or IP address
 * @method string getPort();
 * TCP/IP port of PostgreSQL/MySQL
 * @method string getCharset();
 * Database charset
 * @method string getOptions();
 * The PDO array for connection options, MySQL connection charset, for example
 * @method bool getIsFile();
 * Check PDO for whether it is a file based database connection, for example to a SQLite
 * database file, or not
 * @method bool getToMssql();
 * If we want to convert Queries in MySql syntax to MS-SQL syntax.
 * Yes, there are some differences in query syntax.
 * @method bool getToMysql();
 * If we want to convert Queries in MySql syntax to MS-SQL syntax.
 * Yes, there are some differences in query syntax.
 * @method string getPath();
 * The path to open an SQLite database
 */
interface ConfigInterface
{
    /**
     * Setup Connections for each SQL database class
     *
     * @param string $driver - The vendor's SQL database driver name
     * @param array $arguments SQL connection parameters, in the following:
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
     */
    public static function initialize(string $driver = '', array $arguments = null);
}
