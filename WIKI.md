Overview
===

__ezsql__
-----

Is a library/widget that makes it very fast and easy for you to use database(s) within your **PHP** scripts, supporting ( **_MySQL_** / **_PostgreSQL_** / **_Microsoft SQL Server_** / **_SQLite3_**), and the **_PDO_** equivalents.

- It is one php file that you include at the top of your script. Then, instead of using standard php database functions listed in the php manual, you use a much smaller (and easier) set of **ezsql**  functions and methods.
- It automatically caches query results and allows you to use easy to understand functions to manipulate and extract them without causing extra server overhead.
- It has excellent debug functions making it lightning-fast to see what’s going on in your SQL code.
- Most **ezsql** functions can return results as *Objects*, *Associative Arrays*, *Numerical Arrays* or *Json Encoded*.
- It can dramatically decrease development time and in most cases will streamline your code and make things run faster as well as making it very easy to debug and optimise your database queries.
- It is a small class and will not add very much overhead to your website.

**_Note:_** _It is assumed that you are familiar with PHP, basic Database concepts and basic SQL constructs._

Quick Examples
---

Note: In all these examples no other code is required other than including:

either

    require 'ez_sql_loader.php';

or

    // composer is required for version 4
    require 'vendor/autoload.php';

___Example 1___

```php
// Select multiple records from the database and print them out..
$users = $db->get_results("SELECT name, email FROM users");
foreach ( $users as $user )
{
 // Access data using object syntax
 echo $user->name;
 echo $user->email;
}
```

___Example 2___

```php
// Get one row from the database and print it out..
$user = $db->get_row("SELECT name,email FROM users WHERE id = 2");

echo $user->name;
echo $user->email;
```

___Example 3___

```php
// Get one variable from the database and print it out..
$var = $db->get_var("SELECT count(\*) FROM users");

echo $var;
```

___Example 4___

```php
// Insert into the database
$db->query("INSERT INTO users (id, name, email) VALUES (NULL,'justin','jv@foo.com')");
```

___Example 5___

```php
// Update the database
$db->query("UPDATE users SET name = 'Justin' WHERE id = 2)");
```

___Example 6___

```php
// Display last query and all associated results
$db->debug();
```

___Example 7___

```php
// Display the structure and contents of any result(s) .. or any variable
$results = $db->get_results("SELECT name, email FROM users");

$db->varDump($results);
```

___Example 8___

```php
// Get 'one column' (based on column index) and print it out..
$names = $db->get_col("SELECT name,email FROM users", 0)

foreach ( $names as $name ) 
{
 echo $name;
}
```

___Example 9___

```php
// Same as above ‘but quicker’
foreach ( $db->get_col("SELECT name,email FROM users", 0) as $name )
{
 echo $name;
}
```

___Example 10___

```php
// Map out the full schema of any given database and print it out..
$db->select("my_database");

foreach ( $db->get_col("SHOW TABLES",0) as $table-name )
{
 $db->debug();
 $db->get_results("DESC $table-name");
}

$db->debug();
```

Introduction
---

When working with databases most of the time you will want to do one of four types of basic operations.

1. _Perform a query such as Insert or Update (without results)_
2. _Get a single variable from the database_
3. _Get a single row from the database_
4. _Get a list of results from the database_

**ezsql** wraps up these four basic actions into four very easy to use functions.

- bool: **`$db->query`**(query)
- var: **`$db->get_var`**(query)
- mixed: **`$db->get_row`**(query)
- mixed: **`$db->get_results`**(query)

With **ezsql** these four functions are all you will need 99.9% of the time. Of course there are also some other useful functions but we will get into those later.

**_Important Note:_** _If you use **ezsql** inside a function you write, you will need to put **global $db;** at the top._
>In version 3 and higher there are global functions available to retrieve the object. **`getInstance()`**, **`tagInstance`**(getTagCreated)

Need more help, try reading these articles: https://wpshout.com/introduction-to-wpdb-why-not/, https://wpreset.com/customize-wpdb-class/, and https://codex.wordpress.org/Class_Reference/wpdb. 

Any articles referencing WordPress database engine is an good source of what kind of ecosystem can be built with the flexible of what this library provides. However, the ease of use and process this library initially fostered, spread to other areas, leading to hard to follow, and bad coding habits by today's standards.

Version 4 of this library attempts to, beside all the additional features, _remove some bad coding styles_, _bring the library to modern coding practices of which_, _follow proper __OOP__ and be __PSR__ compliant_. 

This version further break things introduced in version 3 that broke version 2.1.7. See [CHANGE LOG](https://github.com/ezSQL/ezSQL/blob/master/CHANGELOG.md)

____Installation and Usage____
---


Either: ***for version 3.x***

    composer require ezsql/ezsql=^3.1.2

```php
require 'vendor/autoload.php';
```
```php
// Manually download https://github.com/ezSQL/ezSQL/archive/v3.zip and extract.
require 'ez_sql_loader.php';
```
```php
$db = new ezSQL_****(user, password, database, or, other settings);
```

Or: ***for version 4.x***

    // composer is required for version 4
    composer require ezsql/ezsql

```php
require 'vendor/autoload.php';

$db = Database::initialize('****', [user, password, database, other settings], **optional tag);

// Is same as:
$setting = new Config('****', [user, password, database, other settings]);
$db = new ez_****($settings);
```

>Note: __****__ is one of **mysqli**, **pgsql**, **sqlsrv**, **sqlite3**, or **Pdo**.

**ezsql** functions
---

**$db->get_results** -- get multiple row result set from the database (or previously cached results)

**$db->get_row** -- get one row from the database (or previously cached results)

**$db->get_col** -- get one column from query (or previously cached results) based on column offset

**$db->get_var** -- get one variable, from one row, from the database (or previously cached results)

**$db->query** -- send a query to the database (and if any results, cache them)

**$db->debug** -- print last sql query and returned results (if any)

**$db->vardump** -- print the contents and structure of any variable

**$db->select** -- select a new database to work with

**$db->get_col-info** -- get information about one or all columns such as column name or type

**$db->hide-errors** -- turn **ezsql** error output to browser off

**$db->show-errors** -- turn **ezsql** error output to browser on

**$db->escape** -- Format a string correctly to stop accidental mal formed queries under all PHP conditions

**$db = new db** -- Initiate new db object.

**ezsql** variables
---

**$db->num-rows** – Number of rows that were returned (by the database) for the last query (if any)

**$db->insert-id** -- ID generated from the AUTO-INCRIMENT of the previous INSERT operation (if any)

**$db->rows-affected** -- Number of rows affected (in the database) by the last INSERT, UPDATE or DELETE (if any)

**$db->num-queries** -- Keeps track of exactly how many 'real' (not cached) queries were executed during the lifetime of the current script

**$db->debug-all** – If set to true (i.e. $db->debug-all = true;) Then it will print out ALL queries and ALL results of your script.

**$db->cache-dir –** Path to mySQL caching dir.

**$db->cache-queries –** Boolean flag (see mysql/disk-cache-example.php)

**$db->cache-inserts –** Boolean flag (see mysql/disk-cache-example.php)

**$db->use-disk-cache –** Boolean flag (see mysql/disk-cache-example.php)

**$db->cache-timeout –** Number in hours (see mysql/disk-cache-example.php)

____db = new db____

__$db = new db__ -- Initiate new db object. Connect to a database server. Select a database.

____Description____

**$db = new db**(string username, string password, string database name, string database host)

Does three things. (1) Initiates a new db object. (2) Connects to a database server. (3) Selects a database. You can also re-submit this command if you would like to initiate a second db object. This is interesting because you can run two concurrent database connections at the same time. You can even connect to two different servers at the same time if you want to.

_Note: For the sake of efficiency it is recommended that you only run one instance of the **db** object and use **$db->select** to switch between different databases on the same server connection._

Example

```php
 // Initiate new database object..
$db2 = new db(”user-name”, ”user-password”, ”database-name”, “database-host”); // version 2 and 3

 // Perform some kind of query..
 $other_db_tables = $db2->get_results(“SHOW TABLES”);

 // You can still query the database you were already connected to..
 $existing_connection_tables = $db->get_results(“SHOW TABLES”);

 // Print the results from both of these queries..
 $db->debug();
 $db2->debug();
 ```

__$db->select__ - for **mysql** only.

___$db->select___ -- select a new database to work with

____Description____

bool **$db->select**(string database name)

**$db->select**() selects a new database to work with using the current database connection as created with **$db = new db**.

Example

```php
 // Get a users name from the user’s database (as initiated with $db = new db)..
$user-name = $db->get_var(“SELECT name FROM users WHERE id = 22”) ;

 // Select the database stats..
$db->select(“stats”);

 // Get a users name from the user’s database..
$total-hours = $db->get_var(“SELECT sum(time-logged-in) FROM user-stats WHERE user = ‘$user-name’”) ;

 // Re-select the ‘users’ database to continue working as normal..
$db->select(“users”);
```

____$db->query____

__$db->query__ -- send a query to the database (and if any results, cache them)

____Description____

bool **$db->query**(string query)

**$db->query**() sends a query to the currently selected database. It should be noted that you can send any type of query to the database using this command. If there are any results generated they will be stored and can be accessed by any **ezsql** function as long as you use a null query. If there are results returned the function will return **true** if no results the return will be **false**

Example 1

```php
 // Insert a new user into the database..
$db->query(“INSERT INTO users (id,name) VALUES (1, ’Amy’)”);
```

Example 2

```php
 // Update user into the database..
$db->query(“UPDATE users SET name = ‘Tyson’ WHERE id = 1”);
```

Example 3

```php
 // Query to get full user list..
$db->query(“SELECT name,email FROM users”) ;

 // Get the second row from the cached results by using a **null** query..
$user->details = $db->get->row(null, OBJECT, 1);

 // Display the contents and structure of the variable $user-details..
$db->varDump($user->details);
```

**$db->get_var**

$db->get_var -- get one variable, from one row, from the database (or previously cached results)

**Description**

var **$db->get_var**(string query / null [,int column offset[, int row offset])

**$db->get_var**() gets one single variable from the database or previously cached results. This function is very useful for evaluating query results within logic statements such as **if** or **switch**. If the query generates more than one row the first row will always be used by default. If the query generates more than one column the leftmost column will always be used by default. Even so, the full results set will be available within the array $db->last-results should you wish to use them.

Example 1

```php
 // Get total number of users from the database..
$num-users = $db->get_var(“SELECT count(\*) FROM users”) ;
```

Example 2

```php
 // Get a users email from the second row of results (note: col 1, row 1 [starts at 0])..
$user-email = $db->get_var(“SELECT name, email FROM users”,1,1) ;

 // Get the full second row from the cached results (row = 1 [starts at 0])..
$user = $db->get_row(null,OBJECT,1);

 // Both are the same value..
 echo $user-email;
 echo $user->email;
```

Example 3

```php
 // Find out how many users there are called Amy..
if ( $n = $db->get_var(“SELECT count(\*) FROM users WHERE name = ‘Amy’”) ) 
{
 // If there are users then the if clause will evaluate to true. This is useful because
// we can extract a value from the DB and test it at the same time.
echo “There are $n users called Amy!”;
} else {
// If there are no users then the if will evaluate to false..
echo “There are no users called Amy.”;
}
```

Example 4

```php
 // Match a password from a submitted from a form with a password stored in the DB
if ( $pwd-from-form == $db->get_var(“SELECT pwd FROM users WHERE name = ‘$name-from-form’”) )
{
 // Once again we have extracted and evaluated a result at the same time..
echo “Congratulations you have logged in.”;
} else {
 // If has evaluated to false..
echo “Bad password or Bad user ID”;
}
```

**$db->get_row**

$db->get_row -- get one row from the database (or previously cached results)

**Description**

object **$db->get_row**(string query / null [, OBJECT / ARRAY-A / ARRAY-N [, int row offset]])

**$db->get_row**() gets a single row from the database or cached results. If the query returns more than one row and no row offset is supplied the first row within the results set will be returned by default. Even so, the full results will be cached should you wish to use them with another **ezsql** query.

Example 1

```php
 // Get a users name and email from the database and extract it into an object called user..
$user = $db->get_row(“SELECT name,email FROM users WHERE id = 22”) ;

 // Output the values..
 echo “$user->name has the email of $user->email”;

 Output:
  Amy has the email of amy@foo.com
```

Example 2

```php
// Get users name and date joined as associative array
// (Note: we must specify the row offset index in order to use the third argument)
 $user = $db->get_row(“SELECT name, UNIX-TIMESTAMP(my-date-joined) as date-joined FROM users WHERE id = 22”,ARRAY-A) ;

// Note how the unix-timestamp command is used with **as** this will ensure that the resulting data will be easily
// accessible via the created object or associative array. In this case $user[‘date-joined’] (object would be $user->date-joined)
 echo $user[‘name’] . “ joined us on ” . date(“m/d/y”,$user[‘date-joined’]);

 Output:
  Amy joined us on 05/02/01
```

Example 3

```php
 // Get second row of cached results.
 $user = $db->get_row(null,OBJECT,1);

 // Note: Row offset starts at 0
 echo “$user->name joined us on ” . date(“m/d/y”,$user->date-joined);

 Output:
  Tyson joined us on 05/02/02
```

Example 4

```php
 // Get one row as a numerical array..
 $user = $db->get_row(“SELECT name,email,address FROM users WHERE id = 1”,ARRAY-N);

 // Output the results as a table..
 echo “<table>”;

 for ( $i=1; $i <= count($user); $i++ )
 {
  echo “<tr><td>$i</td><td>$user[$I]</td></tr>”;
 }

 echo “</table>”;

 Output:
  1 amy
  2 amy@foo.com
  3 123 Foo Road
```

**$db->get_results**
$db->get_results – get multiple row result set from the database (or previously cached results)

**Description**
array **$db->get_results**(string query / null [, OBJECT / ARRAY-A / ARRAY-N ] )

**$db->get_row**() gets multiple rows of results from the database based on _query_ and returns them as a multi dimensional array. Each element of the array contains one row of results and can be specified to be either an object, associative array or numerical array. If no results are found then the function returns false enabling you to use the function within logic statements such as **if.**

Example 1 – Return results as objects (default)

Returning results as an object is the quickest way to get and display results. It is also useful that you are able to put $object->var syntax directly inside print statements without having to worry about causing php parsing errors.

```php
 // Extract results into the array $users (and evaluate if there are any results at the same time)..
if ( $users = $db->get_results(“SELECT name, email FROM users”) )
{
 // Loop through the resulting array on the index $users[n]
  foreach ( $users as $user )
  {
 // Access data using column names as associative array keys
   echo “$user->name - $user->email<br\>”;
  }
} else {
 // If no users were found then **if** evaluates to false..
echo “No users found.”;
}

 Output:
  Amy - amy@hotmail.com
  Tyson - tyson@hotmail.com
 ```

Example 2 – Return results as associative array
Returning results as an associative array is useful if you would like dynamic access to column names. Here is an example.

```php
 // Extract results into the array $dogs (and evaluate if there are any results at the same time)..
if ( $dogs = $db->get_results(“SELECT breed, owner, name FROM dogs”, ARRAY-A) )
{
 // Loop through the resulting array on the index $dogs[n]
  foreach ( $dogs as $dog-detail )
  {
 // Loop through the resulting array
   foreach ( $dogs->detail as $key => $val )
   {
 // Access and format data using $key and $val pairs..
    echo “<b>” . ucfirst($key) . “</b>: $val<br>”;
   }
 // Do a P between dogs..
   echo “<p>”;
  }
} else {
 // If no users were found then **if** evaluates to false..
echo “No dogs found.”;
}

Output:
 Breed: Boxer
 Owner: Amy
 Name: Tyson
 Breed: Labrador
 Owner: Lee
 Name: Henry
 Breed: Dachshund
 Owner: Mary
 Name: Jasmine
```

Example 3 – Return results as numerical array
Returning results as a numerical array is useful if you are using completely dynamic queries with varying column names but still need a way to get a handle on the results. Here is an example of this concept in use. Imagine that this script was responding to a form with $type being submitted as either ‘fish’ or ‘dog’.

```php
 // Create an associative array for animal types..
  $animal = array ( “fish” => “num-fins”, “dog” => “num-legs” );

 // Create a dynamic query on the fly..
  if ( $results = $db->(“SELECT $animal[$type] FROM $type”, ARRAY-N))
  {
   foreach ( $results as $result )
   {
    echo “$result[0]<br>”;
   }
  } else {
   echo “No $animal\\s!”;
  }

Output:
    4
    4
    4
Note: The dynamic query would be look like one of the following...
· SELECT num-fins FROM fish
· SELECT num-legs FROM dogs

It would be easy to see which it was by using $db->debug(); after the dynamic query call.
```

**$db->debug**

$db->debug – print last sql query and returned results (if any)

**Description**

**$db->debug**(void)

**$db->debug**() prints last sql query and its results (if any)

Example 1
If you need to know what your last query was and what the returned results are here is how you do it.

```php
 // Extract results into the array $users..
$users = $db->get_results(“SELECT name, email FROM users”);

// See what just happened!
$db->debug();
```

**$db->vardump**

$db->vardump – print the contents and structure of any variable

**Description**

**$db->vardump**(void)

**$db->vardump**() prints the contents and structure of any variable. It does not matter what the structure is be it an object, associative array or numerical array.

Example 1
If you need to know what value and structure any of your results variables are here is how you do it.

```php
 // Extract results into the array $users..
$users = $db->get_results(“SELECT name, email FROM users”);

// View the contents and structure of $users
$db->vardump($users);
```

**$db->get_col**

$db->get_col – get one column from query (or previously cached results) based on column offset

**Description**

**$db->get_col**( string query / null [, int column offset] )

**$db->get_col**() extracts one column as one dimensional array based on a column offset. If no offset is supplied the offset will defualt to column 0. I.E the first column. If a null query is supplied the previous query results are used.

Example 1

```php
 // Extract list of products and print them out at the same time..
foreach ( $db->get_col(“SELECT product FROM product-list”) as $product
{
 echo $product;
}
```

Example 2 – Working with cached results

```php
 // Extract results into the array $users..
$users = $db->get_results(“SELECT \* FROM users”);

// Work out how many columns have been selected..
$last-col-num = $db->num-cols - 1;

// Print the last column of the query using cached results..
foreach ( $db->get_col(null, $last-col-num) as $last-col )
{
 echo $last-col;
}
```

**$db->get_col-info**

$db->get_col-info - get information about one or all columns such as column name or type

**Description**

**$db->get_col-info**(string info-type[, int column offset])

**$db->get_col-info**()returns meta information about one or all columns such as column name or type. If no information type is supplied then the default information type of **name** is used. If no column offset is supplied then a one dimensional array is returned with the information type for ‘all columns’. For access to the full meta information for all columns you can use the cached variable $db->col-info

Available Info-Types

**mySQL**

· name - column name

· table - name of the table the column belongs to

· max-length - maximum length of the column

· not-null - 1 if the column cannot be NULL

· primary-key - 1 if the column is a primary key

· unique-key - 1 if the column is a unique key

· multiple-key - 1 if the column is a non-unique key

· numeric - 1 if the column is numeric

· blob - 1 if the column is a BLOB

· type - the type of the column

· unsigned - 1 if the column is unsigned

· zerofill - 1 if the column is zero-filled

**ibase**

· name - column name

· type - the type of the column

· length - size of column

· alias - undocumented

· relation - undocumented

**MS-SQL / Oracle / Postgress**

· name - column name

· type - the type of the column

· length - size of column

**SQLite**

· name - column name
Example 1

```php
 // Extract results into the array $users..
$users = $db->get_results(“SELECT id, name, email FROM users”);

// Output the name for each column type
foreach ( $db->get_col-info(“name”)  as $name )
{
 echo “$name<br>”;
}

 Output:
  id
  name
  email
```

Example 2

```php
 // Extract results into the array $users..
$users = $db->get_results(“SELECT id, name, email FROM users”);

 // View all meta information for all columns..
 $db->vardump($db->col-info);
```

**$db->hide-errors**

$db->hide-errors – turn **ezsql** error output to browser off

**Description**

**$db->hide-errors**( void )

**$db->hide-errors**() stops error output from being printed to the web client. If you would like to stop error output but still be able to trap errors for debugging or for your own error output function you can make use of the global error array $EZSQL-ERROR.

**_Note:_** _If there were no errors then the global error array $EZSQL-ERROR will evaluate to false. If there were one or more errors then it will have  the following structure. Errors are added to the array in order of being called._

$EZSQL-ERROR[0] = Array
(
  [query] => SOME BAD QUERY
  [error-str] => You have an error in your SQL syntax near ‘SOME BAD QUERY' at line 1
)

$EZSQL-ERROR[1] = Array
(
  [query] => ANOTHER BAD QUERY
  [error-str] => You have an error in your SQL syntax near ‘ANOTHER BAD QUERY' at line 1
)

$EZSQL-ERROR[2] = Array
(
  [query] => THIRD BAD QUERY
  [error-str] => You have an error in your SQL syntax near ‘THIRD BAD QUERY' at line 1
)

Example 1

```php
 // Using a custom error function
$db->hide-errors();

// Make a silly query that will produce an error
$db->query(“INSERT INTO my-table A BAD QUERY THAT GENERATES AN ERROR”);

// And another one, for good measure
$db->query(“ANOTHER BAD QUERY THAT GENERATES AN ERROR”);

// If the global error array exists at all then we know there was 1 or more **ezsql** errors..
if ( $EZSQL-ERROR )
{
 // View the errors
 $db->vardump($EZSQL-ERROR);
} else {
 echo “No Errors”;
}
```

**$db->show_errors**

$db->show_errors – turn **ezsql** error output to browser on

**Description**

**$db->show_errors**( void )

**$db->show_errors**() turns **ezsql** error output to the browser on. If you have not used the function $db->hide-errors this function (show-errors) will have no effect.

**$db->escape**

$db->escape – Format a string correctly in order to stop accidental mal formed queries under all PHP conditions.

**Description**

**$db->escape**( string )

**$db->escape**() makes any string safe to use as a value in a query under all PHP conditions. I.E. if magic quotes are turned on or off. Note: Should not be used by itself to guard against SQL injection attacks. The purpose of this function is to stop accidental mal formed queries.

Example 1

```php
 // Escape and assign the value..
 $title = $db->escape(“Justin’s and Amy’s Home Page”);

 // Insert in to the DB..
$db->query(“INSERT INTO pages (title) VALUES (’$title’)”) ;
```

Example 2

```php
 // Assign the value..
 $title = “Justin’s and Amy’s Home Page”;

 // Insert in to the DB and escape at the same time..
$db->query(“INSERT INTO pages (title) VALUES (’”. $db->escape($title).”’)”) ;
```

Disk Caching

**ezsql** has the ability to cache your queries which can make dynamic sites run much faster.

If you want to cache EVERYTHING just do..

**$db->setUse_Disk_Cache(true);**

**$db->setCache_Queries(true);**

**$db->setCache_Timeout(24);**
