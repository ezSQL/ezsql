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

