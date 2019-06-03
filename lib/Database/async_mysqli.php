<?php
declare(strict_types=1);

namespace ezsql\Database;

use Exception;
use ezsql\Database\ez_mysqli;

class async_mysqli extends ez_mysqli
{
    private $links;

    public function __construct(ConfigInterface $settings = null) 
    {
        if (empty($settings)) {
            throw new Exception(\MISSING_CONFIGURATION);
        }
        
        parent::__construct($settings);
        $this->database = $settings;

        if (empty($GLOBALS['async'.\MYSQLI]))
            $GLOBALS['async'.\MYSQLI] = $this;
        \setInstance($this);

        // Prepare statement usage not possible with async queries.
        $this->prepareOff();
    } // __construct

    public function query_prepared(string $query, array $param = null) {
        return false;
    }

    /**
     * Perform mySQL query and try to determine result value
     *
     * @param string $query
     * @param bool $use_prepare
     * @return bool|mixed
     */
    public function query(string $query, bool $use_prepare = false)
    {		
        // Initialize return
        $this->return_val = 0;

        // Flush cached values..
        $this->flush();

        // For reg expressions
        $query = \trim($query);

        // Log how the function was called
        $this->log_query("\$db->query(\"$query\")");

        // Keep track of the last query for debug..
        $this->last_query = $query;

        // Count how many queries there have been
        $this->num_queries++;

        // Use core file cache function
        if ( $cache = $this->get_cache($query) ) {
            return $cache;
        }

        // If there is no existing database connection then try to connect
        if ( ! isset($this->dbh) || ! $this->dbh ) {
            $this->connect($this->database->getUser(), $this->database->getPassword(), $this->database->getHost());
            $this->select($this->database->getName());
        }
        
        \mysqli_query($this->dbh, $query, \MYSQLI_ASYNC);
        $connection = $this->dbh;
        
        do {
            yield;
            $links = $errors = $reject = array($this->dbh);
			\mysqli_poll($links, $errors, $reject, 0, 1);
		} while (!\in_array($connection, $links, true) && !\in_array($connection, $errors, true) && !\in_array($connection, $reject, true));

        $result = \mysqli_reap_async_query($connection);
        // If there is an error then take note of it..
        if ( $str = \mysqli_error($this->dbh) ) {
            $this->register_error($str);
                    
            // If debug ALL queries
            $this->trace || $this->debug_all ? $this->debug() : null ;
            return false;
        }

        if ($this->processQueryResult($query, $result) === false)
            return false;

        // disk caching of queries
        $this->store_cache($query, $this->is_insert);

        // If debug ALL queries
        $this->trace || $this->debug_all ? $this->debug() : null ;

        return $this->return_val;
    } // query
	
    /**
     * Begin Mysql Transaction
     */
    public function beginTransaction()
    {
        /* turn autocommit off */
        $this->dbh->autocommit(false);
        $this->dbh->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
        $this->isTransactional = true;
    }

    public function commit()
    {
        $this->dbh->commit();
        $this->dbh->autocommit(true);
        $this->isTransactional = false;
    }
    
    public function rollback()
    {
        $this->dbh->rollBack();
        $this->dbh->autocommit(true);
        $this->isTransactional = false;
    }
} // ez_mysqli