**ezsql**
=====
[![Build Status](https://travis-ci.org/ezSQL/ezSQL.svg?branch=master)](https://travis-ci.org/ezSQL/ezSQL)
[![Build status](https://ci.appveyor.com/api/projects/status/6s8oqnoxa2i5k04f/branch/master?svg=true)](https://ci.appveyor.com/project/jv2222/ezsql/branch/master)
[![codecov](https://codecov.io/gh/ezSQL/ezSQL/branch/master/graph/badge.svg)](https://codecov.io/gh/ezSQL/ezSQL)
[![Maintainability](https://api.codeclimate.com/v1/badges/8db71512a019ab280a16/maintainability)](https://codeclimate.com/github/techno-express/ezSQL/maintainability)

Introduction

When working with databases most of the time you will want to do one of four types of basic operations.
1. Perform a query such as Insert or Update (without results)
2. Get a single variable from the database
3. Get a single row from the database
4. Get a list of results from the database

**ezsql** wraps up these four basic actions into four very easy to use functions.

bool $db->query(query)
var $db->get_var(query)
mixed $db->get_row(query)
mixed $db->get_results(query)

With **ezsql** these four functions are all you will need 99.9% of the time. Of course there are also some other useful functions but we will get into those later.

Important Note: If you use **ezsql** inside a function you write, you will need to put global $db; at the top.

Description
------------
Class to make it very easy to deal with database connections.

· **ezsql** is a widget that makes it very fast and easy for you to use database(s) within your PHP scripts ( mysql/ postgreSQL / SQL Server/ SQLite3).

· It is one php file that you include at the top of your script. Then, instead of using standard php database functions listed in the php manual, you use a much smaller (and easier) set of **ezsql**  functions.

· It automatically caches query results and allows you to use easy to understand functions to manipulate and extract them without causing extra server overhead

· It has excellent debug functions making it lightning-fast to see what’s going on in your SQL code

· Most **ezsql** functions can return results as Objects, Associative Arrays, or Numerical Arrays

· It can dramatically decrease development time and in most cases will streamline your code and make things run faster as well as making it very easy to debug and optimise your database queries.

· It is a small class and will not add very much overhead to your website.
 
Note: It is assumed that you are familiar with PHP, basic Database concepts and basic SQL constructs. Even if you are a complete beginner **ezsql** can help you once you have read and understood this tutorial.

Installation
------
`composer require ezsql/ezsql`

`require 'vendor/autoload.php';`


**[Authors/Contributors](https://github.com/ezsql/ezsql/CONTRIBUTORS.md)**
---

Quick Examples..

Note: In all these examples no other code is required other than including ez_sql.php
 
Example 1
 
// Select multiple records from the database and print them out..
$users = $db->get_results("SELECT name, email FROM users");
foreach ( $users as $user )
{
	// Access data using object syntax
	echo $user->name;
	echo $user->email;
}
 
Example 2
 
// Get one row from the database and print it out..
$user = $db->get_row("SELECT name,email FROM users WHERE id = 2");
echo $user->name;
echo $user->email;
 
Example 3
 
// Get one variable from the database and print it out..
$var = $db->get_var("SELECT count(*) FROM users");
echo $var;
 
Example 4
 
// Insert into the database

```php
$db->query("INSERT INTO users (id, name, email) VALUES (NULL,'justin','jv@foo.com')");
```
 
Example 5
 
// Update the database
$db->query("UPDATE users SET name = 'Justin' WHERE id = 2)");
 
Example 6
 
// Display last query and all associated results
$db->debug();
 
Example 7
 
// Display the structure and contents of any result(s) .. or any variable
$results = $db->get_results("SELECT name, email FROM users");
$db->vardump($results);
 
Example 8
 
// Get 'one column' (based on column index) and print it out..
$names = $db->get_col("SELECT name,email FROM users",0)
foreach ( $names as $name )
{
	echo $name;
}
 
Example 9
 
// Same as above ‘but quicker’
foreach ( $db->get_col("SELECT name,email FROM users",0) as $name )
{
	echo $name;
}
 
Example 10
 
// Map out the full schema of any given database and print it out..
$db->select("my_database");
foreach ( $db->get_col("SHOW TABLES",0) as $table_name )
{
	$db->debug();
	$db->get_results("DESC $table_name");
}
$db->debug();

Change Log
==========
Note: This change log isn't being used any more due to automated github tracking

3.08 - Merged fork https://github.com/sjstoelting/ezSQL3 to be current with this repo. 
* Added/Updated PHPunit tests, some marked as incomplete or not fully implemented, SQL drivers not loaded will be skipped.
* Refactor class code to use `spl_autoload_register`. 
  Simply using `require_once "ez_sql_loader.php";` then `$database = new database_driver_class;`. This will allow multi SQLdb to be loaded if need be. 
* Added methods `create_select`, `insert_select`, `update`, `insert`, `replace`, `delete`, and `selecting` an alias for select.
  These are part of the new ezQuery SQL builder class. They are shortcut calls, these new methods will create proper SQL statements, from supplied arguments variable or array, prevent injections, then execute guery, in case of `selecting` execute get_results. 
* Added many additional functions to support ezQuery builder and to easily process SQL prepare statements. Supplied arguments will be replace with necessary placeholder and values added to parameter array.
* All new methods has been fully PHPunit tested under current supported database systems, and should be able to handle most use cases as is.
* Todo: Implement WHERE IN sub selection and shortcut method calls for the remainder of SQL standard statements.

```
ezSQL3 - From Author: Stefanie Janine Stoelting - http://stefanie-stoelting.de

News about ezSQL3 are available at http://stefanie-stoelting.de/ezsql3-news.html

* 3.07 - Added the new class ezSQL_mysql to use mysqli. To update existing projects, just change the class you are using from ezSQL_mysql to ezSQL_mysqli. This class is downward compatible to ezSQL_mysql, but is able to use prepared statements.
* 3.06 - Extended ezSQL_mysql method quick_connect with a charset parameter
* 3.05 - Extended ez_sql_oracleTNS class, that does now support client site connection pooling
* 3.04 - Added a new class for Oracle database connection to get rid of TNSNAMES.ORA configuration files
* 3.03 - Changed error messages, wrong classname used in a messages
* 3.02 - Improved ezSQL_recordset, array results of rows are faster
* 3.01 - Added a class for query result handling. The ezSQL_recordset contains methods that behave like fetch_assoc, fetch_row, and fetch_object
* 3.00 - Changed the code to PHP5, added PHPDoc tags, and added unit tests
```

2.17 - Updates to ezSQL_postgresql (thx Stefanie Janine Stoelting)

2.16 - Added profiling functions to mySQL version & added fix to stop mySQL hanging on very long runnign scripts

2.15 - Fixed long standing bug with $db->rows_affected (thx Pere Pasqual)

2.14 - Added sybase connector by Muhammad Iyas

2.13 - Support for transations. See: http://stackoverflow.com/questions/8754215/ezsql-with-multiple-queries/8781798

2.12 - Added $db->get_set() - Creates a SET nvp sql string from an associative array (and escapes all values)

2.11 - Fixed $db->insert_id in postgress version

2.10 - Added isset($this->dbh) check to orcale version

2.09 - Fixed issues with mysql_real_escape_string not woirkign if no connection 
       (Thanks to Nicolas Vannier)

2.08 - Re-added timer functions that seemed to have disappeared

2.07 - Used mysql_real_escape_string instead of mysql_escape_string

2.02 - Please note, this change log is no longer being used
       please see change_log.htm for changes later than
       2.02

2.01 - Added Disk Caching & Multiple DB connection support

2.00 - Re-factored **ezsql** for Oracle, mySQL & SQLite

        - DB Object is no longer initialized by default
          (makes it easier to use multiple connections)

        - Core **ezsql** functions have been separated from DB
          specific functions (makes it easier to add new databases)

        - Errors are being piped through trigger_error
          (using standard PHP error logging)

		- Abstracted error messages (enabling future translation)

		- Upgraded $db->query error return functionality

		- Added $db->systdate function to abstract mySQL NOW()
		  and Oracle SYSDATE

		Note: For other DB Types please use version 1.26

1.26 - Update (All)

        - Fixed the pesky regular expression that tests for
          an insert/update etc. Now it works even for the most
          weirdly formatted queries! (thx dille)

1.25 - Update (mySQL/Oracle)

        - Optimised $db->query function in both mySQL and Oracle versions.
          Now the return value is working 'as expected' in ALL cases so you
          are always safe using:

          if ( $db->query("some query") )

          No matter if an insert or a select.

          In the case of insert/update the return value is based on the
          number of rows affected (used to be insert id which is
          not valid for an update)

          In the case of select the return value is based on number of
          rows returned.

          Thx Bill Bruggemeyer :)

1.24 - Update (Docs)

        - Now includes tutorial for using EZ Results with Smarty
          templating engine - thx Steve Warwick

1.23 - Update (All PHP versions)

        - Fixed the age old problem of returning false on
          successful insert. $db->query()now returns the insert_id
          if there was a successful insert or false if not. Sorry
          that this took so long to fix!

          Version Affected: mySQL/Porgress/ms-sql/sqlite

        - Added new variable $db->debug_all

          By default it is set to false but if you change it
          to true. i.e.

          include_once "ez_sql.php";
          $db->debug_all = true;

          Then it will print out each and every query and all
          of the results that your script creates.

          Very useful if you want to follow the entire logic
          path of what ALL your queries are doing, but can't
          be bothered to put $db->debug() statements all over
          the place!

        Update (Postgress SQL Version)

         - Our old friend Tom De Bruyne as updated the postgress
           version.

           1) It now works without throwing errors (also thanks Geert Nijpels)

           2) It now, in theory, returns $this->insert_id after an insert.


1.22 - Update (All PHP versions)

          - Added new variable $db->num_queries it keeps track
            of exactly how many 'real' (not cached) queries were
            executed (using **ezsql**) during the lifetime of one script.
            Useful for debugging and optimizing.

          - Put a white table behind the vardump output so that
            it doesn't get lost on dark websites.

1.21 - Update (All Versions)

		- Now 'replace' really does return an insert id..
		  (the 1.19 fix did not complete the job. Doh!)

1.20 - Update (New Version)

		- C++ SQLite version added. Look at ez_demo.cpp.
		  (thanks Brennan Falkner)

1.19 - Update (All Versions)

		- Fixed bug where any string containing the word 'insert',
		  'delete' or 'update' (where those words were not the actual
		  query) was causing unexpected results (thx Simon Willison).

		  The fix was to alter the regular expression to only match
		  queries containing those words at the beginning of the query
		  (with optional whitespace allowed before the words)

		  i.e.

		  THIS:    preg_match("/$word /",strtolower($query))
		  TO THIS: preg_match("/^\\s*$word /",strtolower($query))

		  - Added new sql word 'replace' to the above match pattern
		    so that the $db->insert_id would be also be populated
		    on 'replace' queries (thx Rolf Dahl)

1.18 - Update (All Versions)

		- Added new SQLite version (thanks Brennan Falkner)

		- Fixed new bug that was introduced with bug fix 1.14
		  false was being returned on successful insert update etc.
		  now it is true

1.17 - Update (All Versions)

		- New MS-SQL version added (thanks to Tom De Bruyne)
		- Made the donation request 'less annoying' by making it more subtle!

1.16 - Update (All Versions)

		- Added new function $db->escape()
		  Formats a string correctly to stop accidental
		  mal formed queries under all PHP conditions

1.15 - Bug fixes

		- (Postgress)
		  $this->result = false; was in the wrong place.
		  Fixed! Thanks (Carlos Camiña García)

		- (all versions)
		  Pesky get_var was still returning null instead of 0 in
		  certain cases. Bug fix of !== suggested by Osman

1.14 - Bug fixes

		- (all versions)
		  Added !='' into the conditional return of get_var.
		  because if the result was the number 0 it was not returning anything

		- (mySQL / Interbase / Postgress)
		  Added $this->result = false; if insert / update / delete
		  because it was causing mysql retrieval errors that no one
		  knew about due to the @ signs.

1.13 - Update (All Versions)

		- Swapped 2nd and 3rd argument order.
		- From.. get_row(query, int row offset, output type)
		- To.... get_row(query, output type, int row offset)

1.12 - Update (All Versions)

		- Tweaked the new hide/show error code
		- Made sure the $this->show_errors was always initialised
		- $db->query() function now returns false if there was an SQL error.
		  So that you can now do the following when you hide errors.

		  if ( $db->query("BAD SYNTAX") )
		  {
		  	echo "Bad Query";
		  }

1.11 - Update (All Versions)

		- added $db->hide_errors();
		- added $db->show_errors();
		- added global array $EZSQL_ERROR;

1.10 - Fix (mySQL)

		- Insist that mysql_insert_id(); uses $this->dbh.

1.09 - Bug Fix

		- Oracle version had the wrong number of parameters in the
		  $db = new db(etc,etc,etc,etc) part.

		- Also added var $vardump_called; to all versions.

1.08 - Bug Fix

		- Michael fixed the select function in PostgreSQL version.

1.07 - Bug Fix

		- Added var $debug_called; to all versions.

1.06 - Update

		- Fixed Bug In Oracle Version where an insert was
		  causing an error with OCIFetch
		- New PostgreSQL Version Added by Michael Paesold (mpaesold@gmx.at)

1.05 - Bug Fix (mySQL)

		- Removed repeated piece of code.

1.04 - Update

		- $db->num_rows - variable added (All Versions)
		- $db->rows_affected - variable added ( mySQL / Oracle )
		- New InterBase/FireBase Version Added by LLCedar (llceder@wxs.nl)

1.03 - Update (All Versions)

	Enhancements to vardump..

		- Added display variable type
		- If no value display No Value / False
		- Added this readme file

1.02 - Update (mySQL version)

	Added $db->insert_id to

1.01 - New Version

	Oracle 8 Version as below

1.00 - Initial Release

	Functions..

		- $db->get_results - get multiple row result set from the database (or previously cached results)
		- $db->get_row -- get one row from the database (or previously cached results)
		- $db->get_col - get one column from query (or previously cached results) based on column offset
		- $db->get_var -- get one variable, from one row, from the database (or previously cached results)
		- $db->query -- send a query to the database (and if any results, cache them)
		- $db->debug - print last sql query and returned results (if any)
		- $db->vardump - print the contents and structure of any variable
		- $db->select -- select a new database to work with
		- $db->get_col_info - get information about one or all columns such as column name or type
		- $db = new db -- Initiate new db object.
