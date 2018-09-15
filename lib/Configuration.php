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

    /**
    * Array of supported SQL Drivers
    * @const string
    */
    const _DATABASES = array
        ('mysql', 
        'mysqli',
        'pdo', 
        'pgsql', 
        'postgresql', 
        'recordset', 
        'sqlite', 
        'sqlite3', 
        'mssql', 
        'sqlsrv');

class Configuration
{
    use ezsql\Container;
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
    * Database connection
    * @var resource
    */
    protected $connection;
    /**
     * Constructor - initializing the sql database class
     *     
     * @param string $sqldriver The sql database driver name
     * @param string $user The database user name
     * @param string $password The database users password
     * @param string $db The name of the database
     * @param string $host The host name or IP address of the database server.
     *                       Default is localhost
     * @param string $charset The database charset
     *                        Default is empty string
     */
    public function __construct(string $sqldriver, ...$args)
    {
        if (! in_array(strtolower($sqldriver), _DATABASES) || empty($sqldriver) || empty($args)) {
            throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
        } else {
            $this->driver = $sqldriver;
            $this->user = empty($args[0]) ? $this->user : $args[0];
            $this->password = empty($args[1]) ? $this->password : $args[1];
            $this->db = empty($args[2]) ? $this->db : $args[2];
            $this->host = empty($args[3]) ? $this->host : $args[3];
            $this->charset = empty($args[4]) ? $this->charset : $args[4];
        }
    }
}