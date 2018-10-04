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
namespace ezsql;

    // ezQuery prepare placeholder/positional tag
    const _TAG = '__ez__';
    // Use to set get_result output as json 
    const _JSON = 'json';
 
    /*
    * Operator boolean expressions.
    */
    const EQ  = '=';
    const NEQ = '<>';
    const NE  = '!=';
    const LT  = '<';
    const LTE = '<=';
    const GT  = '>';
    const GTE = '>=';
    
    const _IN = 'IN';
    const _notIN = 'NOT IN';
    const _LIKE = 'LIKE';
    const _notLIKE  = 'NOT LIKE';
    const _BETWEEN = 'BETWEEN';
    const _notBETWEEN = 'NOT BETWEEN';
        
    const _isNULL = 'IS NULL';
    const _notNULL  = 'IS NOT NULL';
    
    /*
    * Combine operators.
    */    
    const _AND = 'AND';
    const _OR = 'OR';
    const _NOT = 'NOT';
    const _andNOT = 'AND NOT';
 
    /**
    * Associative array of supported SQL Drivers, and library
    * @const array 
    */
    const VENDOR = [
        'mysql' => 'ez_mysqli',
        'mysqli' => 'ez_mysqli',
        'pdo' => 'ez_pdo',
        'postgresql' => 'ez_pgsql',
        'pgsql' => 'ez_pgsql',
        'sqlite' => 'ez_sqlite3',
        'sqlite3' => 'ez_sqlite3',
        'mssql' => 'ez_sqlsrv',
        'sqlsrv' => 'ez_sqlsrv'
    ];

    const ALLOWED_KEYS = [
        'host',
        'hostname',
        'user',
        'username',
        'password',
        'database',
        'db',
        'name',
        'dsn',
        'char',
        'charset',
        'path',
        'port',
        'file',
        'filebase',
        'nosql',
        'nomysql',
        'options'
    ];
        
    const KEY_MAP = [
        'host' => 'host',
        'hostname' => 'host',
        'user' => 'user',
        'username' => 'user',
        'pass' => 'password',
        'password' => 'password',
        'database' => 'name',
        'db' => 'name',
        'name' => 'name',
        'dsn' => 'dsn',
        'char' => 'charset',
        'charset' => 'charset',
        'path' => 'path',
        'port' => 'port',
        'file' => 'isfile',
        'filebase' => 'isfile',
        'nosql' => 'to_mysql',
        'nomysql' => 'to_mysql',
        'options' => 'options'
    ];