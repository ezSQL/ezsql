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
namespace ezsql\Database;
use ezsql\Configuration;
use ezsql\Container;
use ezsql\Database\ez_mysqli;
use ezsql\Database\ez_sqlsrv;
use ezsql\Database\ez_pgsql;
use ezsql\Database\ez_pdo;
use ezsql\Database\ez_sqlite3;

class Database extends Container
{
    /**
     * Timestamp for benchmark.
     *
     * @var float
     */
    private $_ts = null;

    /**
     * Database configuration setting 
     * @var Configuration instance
     */
    private $database;

    /**
     * Construct and connect a database driver
     *
     * @param class $settings with sql driver and connection parameters
     */    
    public function __construct(Configuration $settings)
    { 
        if  (empty($settings) || (!$settings instanceof Configuration)) {
            throw new Exception('<b>Fatal Error:</b> Missing configuration details to connect to database');
        } else {
            $this->_ts = microtime();
            parent::__construct();
            $this->database = $settings;
            if (empty($GLOBALS['db_'.$this->database->driver])) {
                $GLOBALS['db_'.$this->database->driver] = $this->get(_DATABASES[$this->database->driver], $this->database);
            }
        }
    }
    
    /**
     * Print-out a memory used benchmark.
     *
     * @return float Time elapsed.
     */
    public function benchmark()
    {
        return [
            'start'  => $this->_ts,
            'elapse' => microtime() - $this->_ts,
            'memory' => memory_get_usage(true),
        ];
    }
}