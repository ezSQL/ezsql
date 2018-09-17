<?php 
/**
 * Author:  Lawrence Stubbs <technoexpressnet@gmail.com>
 *
 * Important: Verify that every feature you use will work with your database vendor.
 * ezSQL Query Builder will attempt to validate the generated SQL according to standards.
 * Any errors will return an boolean false, and you will be responsible for handling.
 *
 * ezQuery does no validation whatsoever if certain features even work with the
 * underlying database vendor. 
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
namespace ezsql\Configuration;
use const ezsql\ezFunctions\_DATABASES;

class Configuration
{
     /**
     * Database Sql driver name
     * @var string
     */
    protected $driver;

     /**
     * Database user name
     * @var string
     */
    protected $user;

    /**
     * Database password for the given user
     * @var string
     */
    protected $password;

    /**
     * Database name
     * @var string
     */
    protected $db;

    /**
     * Host name or IP address
     * @var string
     */
    protected $host = 'localhost';

    /**
     * Database charset
     * @var string Default is utf8
     */
    protected $charset = 'utf8';

    /**
     * The PDO connection parameter string, database server in the DSN parameters   
     * @var string Default is empty string
     */
    protected $dsn = '';

    /**
     * The PDO array for connection options, MySQL connection charset, for example
     * @var array
     */
    protected $options = array();
    
    /**
     * Whether it is a file based database connection, for example to a SQLite
     * database file, or not
     * @var boolean Default is false
     */
    protected $isFileBased = false;

    /**
    * TCP/IP port of PostgreSQL
    * @var string Default is port 5432
    */
    protected $port = '5432';

    /**
    * If we want to convert Queries in MySql syntax to MS-SQL syntax. 
    * Yes, there are some differences in query syntax.
    */
    protected $convert_mySql = true;

    /**
    * The path to open an SQLite database
    */
    protected $path;

    /**
     * Constructor - initializing the SQL database class
     *     
     * @param string $sqldriver     The sql database driver name
     * 
     * @param string $path          /args[0]    The path to open an SQLite database
     * @param string $dsn           /args[0]    The PDO DSN connection parameter string
     * 
     * @param string $user          /args[0][1] The database user name
     * @param string $password      /args[1][2] The database users password
     * 
     * @param string $db            /args[1][2] The name of the database
     * @param string $host          /args[3]    The host name or IP address of the database server, Default is localhost
     * @param string $charset       /args[4]    The database charset, Default is empty string
     * 
     * @param array $options        /args[3]    Array for setting connection options as MySQL
     * @param boolean $isFileBased  /args[4]    File based databases like SQLite don't need user and password, 
     *                                          work with path in the dsn parameter
     * @param string $port          /args[4]    The PostgreSQL database TCP/IP port, Default is 5432
     */
    public function __construct(string $sqldriver, ...$args)
    {
        $sql = strtolower($sqldriver);
        if ( ! class_exists ('ezsqlModel') ) {
            throw new Exception('<b>Fatal Error:</b> This configuration requires ezsqlModel (ezsqlModel.php) to be included/loaded before it can be used');
        } elseif (!array_key_exists($sql, \ezsql\ezFunctions\_DATABASES) || empty($sql) || empty($args) || (count($args)<3)) {
            if ((($sql == 'sqlite3') || ($sql == 'sqlite')) && count($args)==2) {
                if ( ! class_exists ('SQLite3') ) 
                    throw new Exception('<b>Fatal Error:</b> ez_sqlite3 requires SQLite3 Lib to be compiled and or linked in to the PHP engine');
                $this->driver = $sql;
                $this->path = empty($args[0]) ? $this->path : $args[0];
                $this->db = empty($args[1]) ? $this->db : $args[1];
            } else
                throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
        } else {
            $this->driver = $sql;
            $this->user = empty($args[0]) ? $this->user : $args[0];
            $this->password = empty($args[1]) ? $this->password : $args[1];
            $this->db = empty($args[2]) ? $this->db : $args[2];
            $this->host = empty($args[3]) ? $this->host : $args[3];
            if ($sql == 'pdo') 
            {
                if ( ! class_exists ('PDO') )
                    throw new Exception('<b>Fatal Error:</b> ez_pdo requires PDO Lib to be compiled and or linked in to the PHP engine');
                $this->dsn = empty($args[0]) ? '' : $args[0];
                $this->user = empty($args[1]) ? '' : $args[1];
                $this->password = empty($args[2]) ? '' : $args[2];
                $this->options = empty($args[3]) ? $this->options : $args[3];
                $this->isFileBased = empty($args[4]) ? $this->isFileBased : $args[4];           
            } 
            elseif (($sql == 'postgresql') || ($sql == 'pgsql')) 
            {
                if ( ! function_exists ('pg_connect') )
                    throw new Exception('<b>Fatal Error:</b> ez_pgsql requires PostgreSQL Lib to be compiled and or linked in to the PHP engine');
                $this->database->port = empty($args[4]) ? $this->database->port : $args[4];
            } 
            elseif (($sql == 'sqlsrv') || ($sql == 'mssql')) 
            {
                if ( ! function_exists ('sqlsrv_connect') ) 
                    throw new Exception('<b>Fatal Error:</b> ez_sqlsrv requires the php_sqlsrv.dll or php_pdo_sqlsrv.dll to be installed. Also enable MS-SQL extension in PHP.ini file ');
                $this->convert_mySql = empty($args[4]) ? $this->convert_mySql : $args[4];
            } 
            elseif (($sql == 'mysqli') || ($sql == 'mysql')) 
            {
                if ( ! function_exists ('mysqli_connect') ) 
                    throw new Exception('<b>Fatal Error:</b> ez_mysql requires mySQLi Lib to be compiled and or linked in to the PHP engine');
                $charset = ! empty($args[4]) ? $args[4] : '';
                $this->charset = empty($charset) ? $this->charset : strtolower(str_replace('-', '', $charset));
            }
        }
    }
}