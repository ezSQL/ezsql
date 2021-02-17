<?php

declare(strict_types=1);

namespace ezsql;

use Exception;
use ezsql\ConfigAbstract;
use ezsql\ConfigInterface;

class Config extends ConfigAbstract implements ConfigInterface
{
    public function __construct(string $driver = '', array $arguments = null)
    {
        $sql = \strtolower($driver);
        if (!\array_key_exists($sql, \VENDOR) || empty($arguments)) {
            throw new Exception(\MISSING_CONFIGURATION);
        } else {
            $this->setDriver($sql);
            if ($sql == \Pdo) {
                $this->setupPdo($arguments);
            } elseif ($sql == \POSTGRESQL) {
                $this->setupPgsql($arguments);
            } elseif ($sql == \SQLSRV) {
                $this->setupSqlsrv($arguments);
            } elseif ($sql == \MYSQLI) {
                $this->setupMysqli($arguments);
            } elseif ($sql == \SQLITE3) {
                $this->setupSqlite3($arguments);
            }
        }
    }

    public static function initialize(string $driver = '',  array $arguments = null)
    {
        return new self($driver, $arguments);
    }

    private function setupMysqli($args)
    {
        if (!\function_exists('mysqli_connect'))
            throw new Exception('<b>Fatal Error:</b> ez_mysql requires mySQLi Lib to be compiled and or linked in to the PHP engine');

        if (\count($args) >= 3) {
            $this->setUser($args[0]);
            $this->setPassword($args[1]);
            $this->setName($args[2]);
            $this->setHost(empty($args[3]) ? $this->getHost() : $args[3]);
            $this->setPort(empty($args[4]) ? '3306' : $args[4]);
            $charset = !empty($args[5]) ? $args[5] : '';
            $this->setCharset(empty($charset) ? $this->getCharset() : \strtolower(\str_replace('-', '', $charset)));
        } else
            throw new Exception(\MISSING_CONFIGURATION);
    }

    private function setupPdo($args)
    {
        if (!\class_exists('PDO'))
            throw new Exception('<b>Fatal Error:</b> ez_pdo requires PDO Lib to be compiled and or linked in to the PHP engine');
        if (\count($args) >= 3) {
            $this->setDsn($args[0]);
            $this->setUser($args[1]);
            $this->setPassword($args[2]);
            $this->setOptions(empty($args[3]) ? $this->getOptions() : $args[3]);
            $this->setIsFile(empty($args[4]) ? $this->getIsFile() : $args[4]);
        } else
            throw new Exception(\MISSING_CONFIGURATION);
    }

    private function setupSqlsrv($args)
    {
        if (!\function_exists('sqlsrv_connect'))
            throw new Exception('<b>Fatal Error:</b> ez_sqlsrv requires the php_sqlsrv.dll or php_pdo_sqlsrv.dll to be installed. Also enable MS-SQL extension in PHP.ini file ');

        if (\count($args) >= 3) {
            $this->setUser($args[0]);
            $this->setPassword($args[1]);
            $this->setName($args[2]);
            $this->setHost(empty($args[3]) ? $this->getHost() : $args[3]);
            $this->setToMssql(empty($args[4]) ? $this->getToMssql() : $args[4]);
        } else
            throw new Exception(\MISSING_CONFIGURATION);
    }

    private function setupPgsql($args)
    {
        if (!\function_exists('pg_connect'))
            throw new Exception('<b>Fatal Error:</b> ez_pgsql requires PostgreSQL Lib to be compiled and or linked in to the PHP engine');

        if (count($args) >= 3) {
            $this->setUser($args[0]);
            $this->setPassword($args[1]);
            $this->setName($args[2]);
            $this->setHost(empty($args[3]) ? $this->getHost() : $args[3]);
            $this->setPort(empty($args[4]) ? '5432' : $args[4]);
        } else
            throw new Exception(\MISSING_CONFIGURATION);
    }

    private function setupSqlite3($args)
    {
        if (!\class_exists('SQLite3'))
            throw new Exception('<b>Fatal Error:</b> ez_sqlite3 requires SQLite3 Lib to be compiled and or linked in to the PHP engine');

        if (\count($args) == 2) {
            $this->setPath($args[0]);
            $this->setName($args[1]);
        } else
            throw new Exception(\MISSING_CONFIGURATION);
    }
}
