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

namespace ezsql;

abstract class ConfigAbstract 
{
    /**
    * Database Sql driver name
    * @var string
    */
    private $driver;

    /**
    * Database user name
    * @var string
    */
    private $user = '';

    /**
    * Database password for the given user
    * @var string
    */
    private $password = '';

    /**
    * Database name
    * @var string
    */
    private $name = '';

    /**
    * Host name or IP address
    * @var string
    */
    private $host = 'localhost';

    /**
    * Database charset
    * @var string Default is utf8
    */
    private $charset = 'utf8';

    /**
    * The PDO connection parameter string, database server in the DSN parameters   
    * @var string Default is empty string
    */
    private $dsn = '';

    /**
    * The PDO array for connection options, MySQL connection charset, for example
    * @var array
    */
    private $options = array();
    
    /**
    * Check PDO for whether it is a file based database connection, for example to a SQLite
    * database file, or not
    * @var boolean Default is false
    */
    private $isfile = false;

    /**
    * TCP/IP port of PostgreSQL
    * @var string Default is port 5432
    */
    private $port = '5432';

    /**
    * If we want to convert Queries in MySql syntax to MS-SQL syntax. 
    * Yes, there are some differences in query syntax.
    */
    private $tomysql = true;

    /**
    * The path to open an SQLite database
    */
    private $path = '';
        
	/**
	* Use for Calling Non-Existent Functions, handling Getters and Setters
	* @method set/get{property} - a property that needs to be accessed 
	*
	* @property-read function
	* @property-write args
	*
	* @return mixed
	*/
	public function __call($function, $args)
	{
		$prefix = \substr($function, 0, 3);
		$property = \strtolower(substr($function, 3, \strlen($function)));
		if (($prefix == 'set') && \property_exists($this, $property)) {
			$this->$property = $args[0];
		} elseif (($prefix == 'get') && \property_exists($this, $property)) {
	 		return $this->$property;
		} else {
			throw new \Exception("$function does not exist");
		}
	}
}