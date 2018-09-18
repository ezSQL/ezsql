<?php 
/**
 * Author:  Lawrence Stubbs <technoexpressnet@gmail.com>
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */
declare(strict_types=1);

namespace ezsql\Configuration;
use ezsql\ConfigAbstract;
use const ezsql\Constants\_DATABASES as VENDOR;

class Configuration implements ConfigAbstract
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
    public function __construct(string $driver, ...$args)
    {
        $sql = strtolower($driver);
        if ( ! class_exists ('ezsqlModel') ) {
            throw new Exception('<b>Fatal Error:</b> This configuration requires ezsqlModel (ezsqlModel.php) to be included/loaded before it can be used');
        } elseif (!array_key_exists($sql, VENDOR) || empty($sql) || empty($args) || (count($args)<3)) {
            if ((($sql == 'sqlite3') || ($sql == 'sqlite')) && count($args)==2) {
                if ( ! class_exists ('SQLite3') ) 
                    throw new Exception('<b>Fatal Error:</b> ez_sqlite3 requires SQLite3 Lib to be compiled and or linked in to the PHP engine');
                $this->setDriver($sql);
                $this->setPath(empty($args[0]) ? $this->getPath() : $args[0]);
                $this->setName(empty($args[1]) ? $this->getName() : $args[1]);
            } else
                throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
        } else {
            $this->setDriver($sql);
            $this->setUser(empty($args[0]) ? $this->getUser() : $args[0]);
            $this->setPassword(empty($args[1]) ? $this->getPassword() : $args[1]);
            $this->setName(empty($args[2]) ? $this->getName() : $args[2]);
            $this->setHost(empty($args[3]) ? $this->getHost() : $args[3]);
            if ($sql == 'pdo') 
            {
                if ( ! class_exists ('PDO') )
                    throw new Exception('<b>Fatal Error:</b> ez_pdo requires PDO Lib to be compiled and or linked in to the PHP engine');           
                $this->setDsn(empty($args[0]) ? $this->getDsn() : $args[0]);
                $this->setUser(empty($args[1]) ? $this->getUser() : $args[1]);
                $this->setPassword(empty($args[2]) ? $this->getPassword() : $args[2]);
                $this->setOptions(empty($args[3]) ? $this->getOptions() : $args[3]);
                $this->setIsFile(empty($args[4]) ? $this->getIsFile() : $args[4]);                    
            } 
            elseif (($sql == 'postgresql') || ($sql == 'pgsql')) 
            {
                if ( ! function_exists ('pg_connect') )
                    throw new Exception('<b>Fatal Error:</b> ez_pgsql requires PostgreSQL Lib to be compiled and or linked in to the PHP engine');
                $this->database->setPort(empty($args[4]) ? $this->database->getPort() : $args[4]);
            } 
            elseif (($sql == 'sqlsrv') || ($sql == 'mssql')) 
            {
                if ( ! function_exists ('sqlsrv_connect') ) 
                    throw new Exception('<b>Fatal Error:</b> ez_sqlsrv requires the php_sqlsrv.dll or php_pdo_sqlsrv.dll to be installed. Also enable MS-SQL extension in PHP.ini file ');
                $this->setTo_mySql(empty($args[4]) ? $this->getTo_mySql() : $args[4]);
            } 
            elseif (($sql == 'mysqli') || ($sql == 'mysql')) 
            {
                if ( ! function_exists ('mysqli_connect') ) 
                    throw new Exception('<b>Fatal Error:</b> ez_mysql requires mySQLi Lib to be compiled and or linked in to the PHP engine');
                $charset = !empty($args[4]) ? $args[4] : '';
                $this->setCharset(empty($charset) ? $this->getCharset() : strtolower(str_replace('-', '', $charset)));
            }
        }
    }
}