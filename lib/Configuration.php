<?php 

declare(strict_types=1);

namespace ezsql;

use Exception;
use ezsql\ConfigAbstract;

class Configuration extends ConfigAbstract
{
    /**
     * Constructor - initializing the SQL database class
     *     
     * @param string $driver     The sql database driver name
     * 
     * @param string $path  /args[0]        The path to open an SQLite database
     * @param string $dsn   /args[0]        The PDO DSN connection parameter string
     * 
     * @param string $user  /args[0][1]     The database user name
     * @param string $password  /args[1][2] The database users password
     * 
     * @param string $name  /args[1][2]     The name of the database
     * @param string $host  /args[3]        The host name or IP address of the database server, Default is localhost
     * @param string $charset   /args[4]    The database charset, Default is empty string
     * 
     * @param array $options    /args[3]    Array for setting connection options as MySQL
     * @param boolean $isFile   /args[4]    File based databases like SQLite don't need user and password, 
     *                                          work with path in the dsn parameter
     * @param string $port  /args[4]        The PostgreSQL database TCP/IP port, Default is 5432
     */
    public function __construct(string $driver, $args)
    {
        $sql = \strtolower($driver);
        if ( ! \class_exists ('ezsqlModel') ) {
            throw new Exception('<b>Fatal Error:</b> This configuration requires ezsqlModel (ezsqlModel.php) to be included/loaded before it can be used');
        } elseif (!\array_key_exists($sql, \VENDOR) || empty($args)) {
            throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
        } else {
            $this->driver = $sql;
            if ($sql == \Pdo) {
                $this->setupPdo($args);            
            } elseif (($sql == \POSTGRESQL) || ($sql == \PGSQL)) {
                $this->setupPgsql($args);
            } elseif (($sql == \SQLSRV) || ($sql == \MSSQL) || ($sql == \SQLSERVER)) {
                $this->setupSqlsrv($args);
            } elseif (($sql == \MYSQLI) || ($sql == \MYSQL)) {
                $this->setupMysqli($args);
            } elseif (($sql == \SQLITE3) || ($sql == \SQLITE)) {
                $this->setupSqlite3($args);
            }
        }
    }

    private function setupMysqli($args) 
    {
        if ( ! \function_exists ('mysqli_connect') ) 
            throw new Exception('<b>Fatal Error:</b> ez_mysql requires mySQLi Lib to be compiled and or linked in to the PHP engine');
        elseif (\is_string($args))
            $this->parseConnectionString($args, ['user', 'name', 'password']);
        elseif (\count($args)>=3) {
            $this->user = empty($args[0]) ? $this->getUser() : $args[0];
            $this->password = empty($args[1]) ? $this->getPassword() : $args[1];
            $this->name = empty($args[2]) ? $this->getName() : $args[2];
            $this->host = empty($args[3]) ? $this->getHost() : $args[3];
            $charset = !empty($args[4]) ? $args[4] : '';
            $this->charset = empty($charset) ? $this->getCharset() : \strtolower(\str_replace('-', '', $charset));
        } else
            throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
    }

    private function setupPdo($args) 
    {
        if ( ! \class_exists ('PDO') )
            throw new Exception('<b>Fatal Error:</b> ez_pdo requires PDO Lib to be compiled and or linked in to the PHP engine');           
        elseif (\is_string($args))
            $this->parseConnectionString($args, ['user', 'dsn', 'password']);
        elseif (\count($args)>=3) {
            $this->dsn = empty($args[0]) ? $this->getDsn() : $args[0];
            $this->user = empty($args[1]) ? $this->getUser() : $args[1];
            $this->password = empty($args[2]) ? $this->getPassword() : $args[2];
            $this->options = empty($args[3]) ? $this->getOptions() : $args[3];
            $this->isFile = empty($args[4]) ? $this->getIsFile() : $args[4];
        } else
           throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
    }

    private function setupSqlsrv($args) 
    {
        if ( ! \function_exists ('sqlsrv_connect') ) 
            throw new Exception('<b>Fatal Error:</b> ez_sqlsrv requires the php_sqlsrv.dll or php_pdo_sqlsrv.dll to be installed. Also enable MS-SQL extension in PHP.ini file ');
        elseif (\is_string($args))
            $this->parseConnectionString($args, ['user', 'name', 'password']);
        elseif (\count($args)>=3) {
            $this->user = empty($args[0]) ? $this->getUser() : $args[0];
            $this->password = empty($args[1]) ? $this->getPassword() : $args[1];
            $this->name = empty($args[2]) ? $this->getName() : $args[2];
            $this->host = empty($args[3]) ? $this->getHost() : $args[3];
            $this->toMysql = empty($args[4]) ? $this->getToMysql() : $args[4];
        } else
            throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
    }

    private function setupPgsql($args) 
    {
        if ( ! \function_exists ('pg_connect') )
            throw new Exception('<b>Fatal Error:</b> ez_pgsql requires PostgreSQL Lib to be compiled and or linked in to the PHP engine');
        elseif (\is_string($args))
            $this->parseConnectionString($args, ['user', 'name', 'password']);
        elseif (count($args)>=3) {
            $this->user = empty($args[0]) ? $this->getUser() : $args[0];
            $this->password = empty($args[1]) ? $this->getPassword() : $args[1];
            $this->name = empty($args[2]) ? $this->getName() : $args[2];
            $this->host = empty($args[3]) ? $this->getHost() : $args[3];
            $this->port = empty($args[4]) ? $this->getPort() : $args[4];
        } else
            throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
    }

    private function setupSqlite3($args) {
        if ( ! \class_exists ('SQLite3') ) 
            throw new Exception('<b>Fatal Error:</b> ez_sqlite3 requires SQLite3 Lib to be compiled and or linked in to the PHP engine');
        elseif (\is_string($args))
            $this->parseConnectionString($args, ['path', 'name']);
        elseif (\count($args)==2) {
            $this->path = empty($args[0]) ? $this->getPath() : $args[0];
            $this->name = empty($args[1]) ? $this->getName() : $args[1];
        } else
            throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
    }

    /**
    * @param string $connectionString
    * @throws Exception If vendor specifics not provided.
    */
    private function parseConnectionString(string $connectionString, array $check_for) 
    {
        $params = \explode(";", $connectionString);

        if (\count($params) === 1) { // Attempt to explode on a space if no ';' are found.
            $params = \explode(" ", $connectionString);
        }

        foreach ($params as $param) {
            list($key, $value) = \array_map("trim", \explode("=", $param, 2) + [1 => null]);

            if (isset(\KEY_MAP[$key])) {
                $key = \KEY_MAP[$key];
            }

            if (!in_array($key, \ALLOWED_KEYS, true)) {
                throw new Exception("Invalid key in connection string: " . $key);
            }

            $this->{$key} = $value;
        }

        foreach ($check_for as $must_have) {
            if(!isset($this->{$must_have}))
                throw new Exception("Required parameters ".$must_have." need to be passed in connection string");
        }

        return true;
    }
}