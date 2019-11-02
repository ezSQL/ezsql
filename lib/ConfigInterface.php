<?php

namespace ezsql;

/**
 * @method void setDriver($args);
 * @method void setDsn($args);
 * @method void setUser($args);
 * @method void setPassword($args);
 * @method void setName($args);
 * @method void setHost($args);
 * @method void setPort($args);
 * @method void setCharset($args);
 * @method void setOptions($args);
 * @method void setIsFile($args);
 * @method void setToMysql($args);
 * @method void setPath($args);
 *
 * @method string getDriver();
 * @method string getDsn();
 * @method string getUser();
 * @method string getPassword()
 * @method string getName();
 * @method string getHost();
 * @method string getPort();
 * @method string getCharset();
 * @method string getOptions();
 * @method bool getIsFile();
 * @method bool getToMysql();
 * @method string getPath();
 */
interface ConfigInterface
{
    /**
     * Setup Connections for each SQL database class
     *
     * @param string $driver
     * @param string|array $arguments
     */
    public static function initialize(string $driver = '', array $arguments = null);
}
