Introduction

**ezsql** Overview [download ez\_sql.zip](http://php.justinvincent.com/download.php?ez_sql)  
To email the creator: justin\_at\_jvmultimedia\_dot\_com

· **ezsql** is a widget that makes it very fast and easy for you to use database(s) within your PHP scripts ( mySQL / Oracle8/9 / InterBase/FireBird / PostgreSQL / MS-SQL / SQLite / SQLite c++).

· It is one php file that you include at the top of your script. Then, instead of using standard php database functions listed in the php manual, you use a much smaller (and easier) set of **ezsql**  functions.

· It automatically caches query results and allows you to use easy to understand functions to manipulate and extract them without causing extra server overhead

· It has excellent debug functions making it lightning-fast to see what’s going on in your SQL code

· Most **ezsql** functions can return results as Objects, Associative Arrays, or Numerical Arrays

· It can dramatically decrease development time and in most cases will streamline your code and make things run faster as well as making it very easy to debug and optimise your database queries.

· It is a small class and will not add very much overhead to your website.

**_Note:_** _It is assumed that you are familiar with PHP, basic Database concepts and basic SQL constructs. Even if you are a complete beginner **ezsql** can help you once you have read and understood [this tutorial](http://www.jvmultimedia.com/portal/node/14)._

Quick Examples..
================

Note: In all these examples no other code is required other than including ez\_sql.php

 ----------------------------------------------------

_Example 1_
===========



// Select multiple records from the database and print them out..

$users = $db->get\_results("SELECT name, email FROM users");

foreach ( $users as $user )

{

 // Access data using object syntax

 echo $user->name;

 echo $user->email;

}



_Example 2_
===========



// Get one row from the database and print it out..

$user = $db->get\_row("SELECT name,email FROM users WHERE id = 2");

echo $user->name;

echo $user->email;



###### Example 3



// Get one variable from the database and print it out..

$var = $db->get\_var("SELECT count(\*) FROM users");

echo $var;



###### Example 4



// Insert into the database

$db->query("INSERT INTO users (id, name, email) VALUES (NULL,'justin','jv@foo.com')");



###### Example 5



// Update the database

$db->query("UPDATE users SET name = 'Justin' WHERE id = 2)");



###### Example 6



// Display last query and all associated results

$db->debug();



###### Example 7



// Display the structure and contents of any result(s) .. or any variable

$results = $db->get\_results("SELECT name, email FROM users");

$db->vardump($results);



###### Example 8



// Get 'one column' (based on column index) and print it out..

$names = $db->get\_col("SELECT name,email FROM users",0)

foreach ( $names as $name )

{

 echo $name;

}



###### Example 9



// Same as above ‘but quicker’

foreach ( $db->get\_col("SELECT name,email FROM users",0) as $name )

{

 echo $name;

}



###### Example 10



// Map out the full schema of any given database and print it out..

$db->select("my\_database");

foreach ( $db->get\_col("SHOW TABLES",0) as $table\_name )

{

 $db->debug();

 $db->get\_results("DESC $table\_name");

}

$db->debug();

Introduction
============

When working with databases most of the time you will want to do one of four types of basic operations.

1. Perform a query such as Insert or Update (without results)

2. Get a single variable from the database

3. Get a single row from the database

4. Get a list of results from the database

**ezsql** wraps up these four basic actions into four very easy to use functions.

bool     **$db->query**(query)

var       **$db->get\_var**(query)

mixed **$db->get\_row**(query)

mixed **$db->get\_results**(query)

With **ezsql** these four functions are all you will need 99.9% of the time. Of course there are also some other useful functions but we will get into those later.

**_Important Note:_** _If you use **ezsql** inside a function you write, you will need to put **global $db;** at the top._

Installation
============

To install **ezsql** download, unzip and install the contents of **[ez\_sql.zip](http://php.justinvincent.com/download.php?ez_sql)** into the same directory within your web server.

**Put the following at the top of your script:**

// Include **ezsql** core

include\_once "ez\_sql\_core.php";

// Include **ezsql** database specific component (in this case mySQL)

include\_once "ez\_sql\_mysql.php";

// Initialise database object and establish a connection

// at the same time - db\_user / db\_password / db\_name / db\_host

$db = new **ezsql**\_mysql('db\_user','db\_password','db\_name','db\_host');

Note: On most systems **localhost** will be fine for the dbhost value. If you are unsure about any of the above settings you should contact your provider or look through your providers documentation.

If you are running on a local machine and have just installed mySQL for the first time, you can probably leave the user name and password empty ( i.e.  = “”) until you set up a mySQL user account.

Running the **ezsql** demo
======================

Once you have installed **ezsql** as described above you can see it in action by running ez\_demo.php via your web browser. To do this simply go to..

http://yourserver.com/install\_path/mysql/demo.php

If you are running your web server on your local machine this will be..

http://127.0.0.1/install\_path/mysql/demo.php

What the demo does… is use **ezsql** functions to map out the table structure of your database (i.e the database you specified at the top of ez\_sql.php). You will be surprised how little code is required to do this when using **ezsql**. I have included it here so you can get a quick feel for the compactness and speed of **ezsql**.

<?php

// Include **ezsql** core

include\_once "ez\_sql\_core.php";

// Include **ezsql** database specific component

include\_once "ez\_sql\_mysql.php";

// Initialise database object and establish a connection

// at the same time - db\_user / db\_password / db\_name / db\_host

$db = new **ezsql**\_mysql('db\_user','db\_password','db\_name','db\_host');

 $my\_tables = $db->get\_results("SHOW TABLES",ARRAY\_N);

 $db->debug();

 foreach ( $my\_tables as $table )

 {

  $db->get\_results("DESC $table\[0\]");

  $db->debug();

 }

?>

The **ezsql** demo explained
========================

**<?php**

This is the standard way to start php executing within your web page.

**include\_once “ez\_sql.php”;**

This is how you include **ezsql** in your script. Normally you include it at the top of your script and from that point forward you have access to any **ezsql** function.

** $my\_tables = $db->get\_results(“SHOW TABLES”,ARRAY\_N);**

get\_results() is how you get ‘a list’ of things from the database using **ezsql**. The list is returned as an array. In this case the std mySQL command  of ‘SHOW TABLES’ is called and the resulting list is stored in a  newly created array $my\_tables.

When using $db->get\_results(), if there are any results, they are always returned as multi-dimensional array. The first dimension is a numbered index. Each of the numbered indexes is either an object, associative array or numerical array containing all the values for ‘one row’.

For example using the switch ARRAY\_A would produce an array that looked something like this.

 $users = $db->get\_results(“SELECT id,name FROM users”,ARRAY\_A);

$users\[0\] = array (“id” => “1”, “name” => “Amy”);

$users\[1\] = array (“id” => “2”, “name” => “Tyson”);

If you wanted a numerical array use the switch ARRAY\_N.

 $users = $db->get\_results(“SELECT id,name FROM users”,ARRAY\_N);

$users\[0\] = array (0 => “1”, 1 => “Amy”);

$users\[1\] = array (0 => “2”, 1 => “Tyson”);

If you wanted an object (which is the default option) you don’t need a switch..

$users = $db->get\_results(“SELECT id,name FROM users”);

$users\[0\]->id = “1”;

$users\[0\]->name = “Amy”;

$users\[1\]->id = “2”;

$users\[1\]->name = “Tyson”;

Results returned as an object make it very easy to work with database results using the numerous array functions that php offers. For example, to loop through results returned as an object all one needs to do is..

$users = $db->get\_results(“SELECT id,name FROM users”);

   foreach( $users as $user )

   {

    echo $user->id;

    echo $user->name;

   }

  If you are 100% sure that there will be results you can skip a step and do this..

   foreach( $db->get\_results(“SELECT id,name FROM users”) as $user )

   {

    echo $user->id;

    echo $user->name;

   }

  If you don’t know whether there will be results or not you can do this..

If ( $users= $db->get\_results(“SELECT id,name FROM users”) )

{

    foreach( $users as $user )

    {

     echo $user->id;

     echo $user->name;

 }

}

else

{

 echo “No results”;

}

**$db->debug();**

This function prints the most recently called sql query along with a well formatted table containing any results that the query generated (if any) and the column info.

**foreach ( $my\_tables as $table)**

This is the standard way to easily loop through an array in php. In this case the array $my\_tables was created with the **ezsql** command $db->get\_results(“SHOW TABLES”,ARRAY\_N). Because of the ARRAY\_N switch the results are returned as a numerical array.

The resulting array will look something like..

$my\_tables\[0\] = array (0 => “users”);

$my\_tables\[1\] = array (0 => “products”);

$my\_tables\[2\] = array (0 => “guestbook”);

** {**

The foreach is looping through each primary element of $my\_tables\[n\] which are in turn numerical arrays, with the format like so..

 array(0 => “value”, 1 => “value”, etc.);

Thus, during the foreach loop of $my\_tables we have access to the value of the first column like so:

 foreach ($my\_tables as $table)

 {

  echo $table\[0\];

 }

If we did the same thing using an associative array it might look like this..

 $users = $db->get\_results(“SELECT id,name FROM users”,ARRAY\_A);

 foreach ( $users as $user )

 {

  echo $user\[‘id’\];

  echo $user\[‘name’\];

 }

But if there were no results foreach might generate a warning. So a safer way to do the above is..

 if ( $users = $db->get\_results(“SELECT id,name FROM users”,ARRAY\_A))

 {

  foreach ( $users as $user )

  {

   echo $user\[‘id’\];

   echo $user\[‘name’\];

  }

 }

 else

 {

  echo “No Users”:

 }

This works because if no results are returned then get\_results() returns false.

**  $db->get\_results(“DESC $table\[0\]”);**

This database query is nested within the foreach loop. Note that we are using the results of the previous call to make a new call. Traditionally you would have to be concerned about using different db\_resource identifiers in a case like this but **ezsql** takes care of that for you, making it very easy to nest database queries.

You may be wondering why I have used a numerical array output and not object or associative array. The reason is because in this case I do not know what the name of the first column will be. So I can make sure that I can always get its value by using numerical array output and targeting the first column by element \[0\].

_FYI: The SQL command SHOW TABLES always names the first column a different value depending on the database being used. If the database was named **users** the column would be called **Tables\_in\_users** if the database was called **customers** the column would be called **Tables\_in\_customers** and so on._

**  $db->debug();**

This function will always print the last query and its results (if any) to the browser. In this case it will be for the above query..

 $db->get\_results(“DESC $table\[0\]”);

You may have noticed that the above get\_results function is not assigning a value. (i.e. $var = val). This is because even if you do not assign the output value of any **ezsql** function the query results are always stored and made ready for any **ezsql** function to use. In this case $db->debug() is displaying the stored results. Then, by calling any **ezsql** function using a **null** query you will be accessing the stored results from the last query. Here is a more detailed example.           

**_Users Table.._**

_amy, amy@foo.com_

_tyson, tyson@foo.com_

 // Any **ezsql** function will store query results..

 $users = $db->get\_results(“SELECT name,email FROM users”);

 // This gets a variable from the above results (offset by $x = 1, $y = 1).

 echo $db->get\_var(null,1,1);

 // Note: Because a **null** query is passed to get\_var it uses results from the previous query.

Output: tyson@foo.com        

** }**

 This closes the foreach loop

**?>**

This stops php executing code

****ezsql** functions**

**$db->get\_results** -- get multiple row result set from the database (or previously cached results)

**$db->get\_row** -- get one row from the database (or previously cached results)

**$db->get\_col** -- get one column from query (or previously cached results) based on column offset

**$db->get\_var** -- get one variable, from one row, from the database (or previously cached results)

**$db->query** -- send a query to the database (and if any results, cache them)

**$db->debug** -- print last sql query and returned results (if any)

**$db->vardump** -- print the contents and structure of any variable

**$db->select** -- select a new database to work with

**$db->get\_col\_info** -- get information about one or all columns such as column name or type

**$db->hide\_errors** -- turn **ezsql** error output to browser off

**$db->show\_errors** -- turn **ezsql** error output to browser on

**$db->escape** -- Format a string correctly to stop accidental mal formed queries under all PHP conditions

**$db = new db** -- Initiate new db object.

****ezsql** variables**

**$db->num\_rows** – Number of rows that were returned (by the database) for the last query (if any)

**$db->insert\_id** -- ID generated from the AUTO\_INCRIMENT of the previous INSERT operation (if any)

**$db->rows\_affected** \-- Number of rows affected (in the database) by the last INSERT, UPDATE or DELETE (if any)

**$db->num\_queries** \-- Keeps track of exactly how many 'real' (not cached) queries were executed during the lifetime of the current script

**$db->debug\_all** – If set to true (i.e. $db->debug\_all = true;) Then it will print out ALL queries and ALL results of your script.

**$db->cache\_dir –** Path to mySQL caching dir.

**$db->cache\_queries –** Boolean flag (see mysql/disk\_cache\_example.php)

**$db->cache\_inserts –** Boolean flag (see mysql/disk\_cache\_example.php)

**$db->use\_disk\_cache –** Boolean flag (see mysql/disk\_cache\_example.php)

**$db->cache\_timeout –** Number in hours (see mysql/disk\_cache\_example.php)

**$db = new db**

$db = new db -- Initiate new db object. Connect to a database server. Select a database.

#### Description

**$db = new db**(string username, string password, string database name, string database host)

Does three things. (1) Initiates a new db object. (2) Connects to a database server. (3) Selects a database. You can also re-submit this command if you would like to initiate a second db object. This is interesting because you can run two concurrent database connections at the same time. You can even connect to two different servers at the same time if you want to.

_Note: For the sake of efficiency it is recommended that you only run one instance of the **db** object and use **$db->select** to switch between different databases on the same server connection._

_Example_
=========

 // Initiate new database object..

$db2 = new db(”user\_name”, ”user\_password”, ”database\_name”, “database\_host”);

 // Perform some kind of query..

 $other\_db\_tables = $db2->get\_results(“SHOW TABLES”);

 // You can still query the database you were already connected to..

 $existing\_connection\_tables = $db->get\_results(“SHOW TABLES”);

 // Print the results from both of these queries..

 $db->debug();

 $db2->debug();

**$db->select**

$db->select -- select a new database to work with

**Description**

bool **$db->select**(string database name)

**$db->select**() selects a new database to work with using the current database connection as created with **$db = new db**.

##### Example

 // Get a users name from the user’s database (as initiated with $db = new db)..

$user\_name = $db->get\_var(“SELECT name FROM users WHERE id = 22”) ;

 // Select the database stats..

$db->select(“stats”);

 // Get a users name from the user’s database..

$total\_hours = $db->get\_var(“SELECT sum(time\_logged\_in) FROM user\_stats WHERE user = ‘$user\_name’”) ;

 // Re-select the ‘users’ database to continue working as normal..

$db->select(“users”);

**$db->query**

$db->query -- send a query to the database (and if any results, cache them)

**Description**

bool **$db->query**(string query)

**$db->query**() sends a query to the currently selected database. It should be noted that you can send any type of query to the database using this command. If there are any results generated they will be stored and can be accessed by any **ezsql** function as long as you use a null query. If there are results returned the function will return **true** if no results the return will be **false**

_Example 1_
===========

 // Insert a new user into the database..

$db->query(“INSERT INTO users (id,name) VALUES (1,’Amy’)”) ;

_Example 2_
===========

 // Update user into the database..

$db->query(“UPDATE users SET name = ‘Tyson’ WHERE id = 1”) ;

_Example 3_
===========

 // Query to get full user list..

$db->query(“SELECT name,email FROM users”) ;

 // Get the second row from the cached results by using a **null** query..

$user\_details = $db->get\_row(null, OBJECT,1);

 // Display the contents and structure of the variable $user\_details..

$db->vardump($user\_details);

**$db->get\_var**

$db->get\_var -- get one variable, from one row, from the database (or previously cached results)

**Description**

var **$db->get\_var**(string query / null \[,int column offset\[, int row offset\])

**$db->get\_var**() gets one single variable from the database or previously cached results. This function is very useful for evaluating query results within logic statements such as **if** or **switch**. If the query generates more than one row the first row will always be used by default. If the query generates more than one column the leftmost column will always be used by default. Even so, the full results set will be available within the array $db->last\_results should you wish to use them.

_Example 1_
===========

 // Get total number of users from the database..

$num\_users = $db->get\_var(“SELECT count(\*) FROM users”) ;

_Example 2_
===========

 // Get a users email from the second row of results (note: col 1, row 1 \[starts at 0\])..

$user\_email = $db->get\_var(“SELECT name, email FROM users”,1,1) ;

 // Get the full second row from the cached results (row = 1 \[starts at 0\])..

$user = $db->get\_row(null,OBJECT,1);

 // Both are the same value..

 echo $user\_email;

 echo $user->email;

_Example 3_
===========

 // Find out how many users there are called Amy..

if ( $n = $db->get\_var(“SELECT count(\*) FROM users WHERE name = ‘Amy’”) )

{

 // If there are users then the if clause will evaluate to true. This is useful because

// we can extract a value from the DB and test it at the same time.

echo “There are $n users called Amy!”;

}

else

{

// If there are no users then the if will evaluate to false..

echo “There are no users called Amy.”;

}

_Example 4_
===========

 // Match a password from a submitted from a form with a password stored in the DB

if ( $pwd\_from\_form == $db->get\_var(“SELECT pwd FROM users WHERE name = ‘$name\_from\_form’”) )

{

 // Once again we have extracted and evaluated a result at the same time..

echo “Congratulations you have logged in.”;

}

else

{

 // If has evaluated to false..

echo “Bad password or Bad user ID”;

}

**$db->get\_row**

$db->get\_row -- get one row from the database (or previously cached results)

**Description**

object **$db->get\_ row**(string query / null \[, OBJECT / ARRAY\_A / ARRAY\_N \[, int row offset\]\])

**$db->get\_row**() gets a single row from the database or cached results. If the query returns more than one row and no row offset is supplied the first row within the results set will be returned by default. Even so, the full results will be cached should you wish to use them with another **ezsql** query.

##### Example 1

 // Get a users name and email from the database and extract it into an object called user..

$user = $db->get\_row(“SELECT name,email FROM users WHERE id = 22”) ;

 // Output the values..

 echo “$user->name has the email of $user->email”;

 _Output:_

_  Amy has the email of amy@foo.com_

### Example 2

 // Get users name and date joined as associative array

// (Note: we must specify the row offset index in order to use the third argument)

 $user = $db->get\_row(“SELECT name, UNIX\_TIMESTAMP(my\_date\_joined) as date\_joined FROM users WHERE id = 22”,ARRAY\_A) ;

 // Note how the unix\_timestamp command is used with **as** this will ensure that the resulting data will be easily

// accessible via the created object or associative array. In this case $user\[‘date\_joined’\] (object would be $user->date\_joined)

 echo $user\[‘name’\] . “ joined us on ” . date(“m/d/y”,$user\[‘date\_joined’\]);

 _Output:_

_  Amy joined us on 05/02/01_

### Example 3

 // Get second row of cached results.

 $user = $db->get\_row(null,OBJECT,1) ;

 // Note: Row offset starts at 0

 echo “$user->name joined us on ” . date(“m/d/y”,$user->date\_joined);

 _Output:_

_  Tyson joined us on 05/02/02_

### Example 4

 // Get one row as a numerical array..

 $user = $db->get\_row(“SELECT name,email,address FROM users WHERE id = 1”,ARRAY\_N);

 // Output the results as a table..

 echo “<table>”;

 for ( $i=1; $i <= count($user); $i++ )

 {

  echo “<tr><td>$i</td><td>$user\[$I\]</td></tr>”;

 }

 echo “</table>”;

 _Output:_

_1_ _amy_

_2_ _amy@foo.com_

_3_ _123 Foo Road_

**$db->get\_results**

$db->get\_results – get multiple row result set from the database (or previously cached results)

**Description**

array **$db->get\_results**(string query / null \[, OBJECT / ARRAY\_A / ARRAY\_N \] )

**$db->get\_row**() gets multiple rows of results from the database based on _query_ and returns them as a multi dimensional array. Each element of the array contains one row of results and can be specified to be either an object, associative array or numerical array. If no results are found then the function returns false enabling you to use the function within logic statements such as **if.**

_Example 1 – Return results as objects (default)_
=================================================

Returning results as an object is the quickest way to get and display results. It is also useful that you are able to put $object->var syntax directly inside print statements without having to worry about causing php parsing errors.

 // Extract results into the array $users (and evaluate if there are any results at the same time)..

if ( $users = $db->get\_results(“SELECT name, email FROM users”) )

{

 // Loop through the resulting array on the index $users\[n\]

  foreach ( $users as $user )

  {

 // Access data using column names as associative array keys

   echo “$user->name - $user->email<br\>”;

  }

}

else

{

 // If no users were found then **if** evaluates to false..

echo “No users found.”;

}

 _Output:_

_ Amy - amy@hotmail.com     _

 _Tyson - tyson@hotmail.com_

_Example 2 – Return results as associative array_
=================================================

Returning results as an associative array is useful if you would like dynamic access to column names. Here is an example.

 // Extract results into the array $dogs (and evaluate if there are any results at the same time)..

if ( $dogs = $db->get\_results(“SELECT breed, owner, name FROM dogs”, ARRAY\_A) )

{

 // Loop through the resulting array on the index $dogs\[n\]

  foreach ( $dogs as $dog\_detail )

  {

 // Loop through the resulting array

   foreach ( $dogs\_detail as $key => $val )

   {

 // Access and format data using $key and $val pairs..

    echo “<b>” . ucfirst($key) . “</b>: $val<br>”;

   }

 // Do a P between dogs..

   echo “<p>”;

  }

}

else

{

 // If no users were found then **if** evaluates to false..

echo “No dogs found.”;

}

 _Output:_

_ **Breed:** Boxer_

_ **Owner:** Amy_

_ **Name:** Tyson_

_ **Breed:** Labrador_

_ **Owner:** Lee_

_ **Name:** Henry_

_ **Breed:** Dachshund_

_ **Owner:** Mary_

_ **Name:** Jasmine_

_Example 3 – Return results as numerical array_
===============================================

 Returning results as a numerical array is useful if you are using completely dynamic queries with varying column

names but still need a way to get a handle on the results. Here is an example of this concept in use. Imagine that this

script was responding to a form with $type being submitted as either ‘fish’ or ‘dog’.

 // Create an associative array for animal types..

  $animal = array ( “fish” => “num\_fins”, “dog” => “num\_legs” );

 // Create a dynamic query on the fly..

  if ( $results = $db->(“SELECT $animal\[$type\] FROM $type”,ARRAY\_N))

  {

   foreach ( $results as $result )

   {

    echo “$result\[0\]<br>”;

   }

  }

  else

  {

   echo “No $animal\\s!”;

  }

 _Output:_

    4

    4

    4

 _ Note: The dynamic query would be look like one of the following..._

· _SELECT num\_fins FROM fish_

· _SELECT num\_legs FROM dogs_

_  It would be easy to see which it was by using $db->debug(); after the dynamic query call._

**$db->debug**

$db->debug – print last sql query and returned results (if any)

**Description**

**$db->debug**(void)

**$db->debug**() prints last sql query and its results (if any)

_Example 1_
===========

If you need to know what your last query was and what the returned results are here is how you do it.

 // Extract results into the array $users..

$users = $db->get\_results(“SELECT name, email FROM users”);

// See what just happened!

$db->debug();

**$db->vardump**

$db->vardump – print the contents and structure of any variable

**Description**

**$db->vardump**(void)

**$db->vardump**() prints the contents and structure of any variable. It does not matter what the structure is be it an object, associative array or numerical array.

_Example 1_
===========

If you need to know what value and structure any of your results variables are here is how you do it.

 // Extract results into the array $users..

$users = $db->get\_results(“SELECT name, email FROM users”);

// View the contents and structure of $users

$db->vardump($users);

**$db->get\_col**

$db->get\_col – get one column from query (or previously cached results) based on column offset

**Description**

**$db->get\_col**( string query / null \[, int column offset\] )

**$db->get\_col**() extracts one column as one dimensional array based on a column offset. If no offset is supplied the offset will defualt to column 0. I.E the first column. If a null query is supplied the previous query results are used.

_Example 1_
===========

 // Extract list of products and print them out at the same time..

foreach ( $db->get\_col(“SELECT product FROM product\_list”) as $product)

{

 echo $product;

}

_Example 2 – Working with cached results_
=========================================

 // Extract results into the array $users..

$users = $db->get\_results(“SELECT \* FROM users”);

// Work out how many columns have been selected..

$last\_col\_num = $db->num\_cols - 1;

// Print the last column of the query using cached results..

foreach ( $db->get\_col(null, $last\_col\_num) as $last\_col )

{

 echo $last\_col;

}

**$db->get\_col\_info**

$db->get\_col\_info - get information about one or all columns such as column name or type

**Description**

**$db->get\_col\_info**(string info-type\[, int column offset\])

**$db->get\_col\_info**()returns meta information about one or all columns such as column name or type. If no information type is supplied then the default information type of **name** is used. If no column offset is supplied then a one dimensional array is returned with the information type for ‘all columns’. For access to the full meta information for all columns you can use the cached variable $db->col\_info

Available Info-Types

**mySQL**

· name - column name
====================

· table - name of the table the column belongs to
=================================================

· max\_length - maximum length of the column
============================================

· not\_null - 1 if the column cannot be NULL
============================================

· primary\_key - 1 if the column is a primary key
=================================================

· unique\_key - 1 if the column is a unique key
===============================================

· multiple\_key - 1 if the column is a non-unique key
=====================================================

· numeric \- 1 if the column is numeric
=======================================

· blob - 1 if the column is a BLOB
==================================

· type - the type of the column
===============================

· unsigned - 1 if the column is unsigned
========================================

· zerofill - 1 if the column is zero-filled
===========================================

**ibase**

· name - column name
====================

· type - the type of the column
===============================

· length - size of column
=========================

· alias - undocumented
======================

· relation \- undocumented
==========================

**MS-SQL / Oracle / Postgress**

· name - column name
====================

· type - the type of the column
===============================

· length - size of column
=========================

**SQLite**

· name - column name
====================

_Example 1_
===========

 // Extract results into the array $users..

$users = $db->get\_results(“SELECT id, name, email FROM users”);

// Output the name for each column type

foreach ( $db->get\_col\_info(“name”)  as $name )

{

 echo “$name<br>”;

}

 Output:

  id

  name

  email

_Example 2_
===========

 // Extract results into the array $users..

$users = $db->get\_results(“SELECT id, name, email FROM users”);

 // View all meta information for all columns..

 $db->vardump($db->col\_info);

**$db->hide\_errors**

$db->hide\_errors – turn **ezsql** error output to browser off

**Description**

**$db->hide\_errors**( void )

**$db->hide\_errors**() stops error output from being printed to the web client. If you would like to stop error output but still be able to trap errors for debugging or for your own error output function you can make use of the global error array $EZSQL\_ERROR.

**_Note:_** _If there were no errors then the global error array $EZSQL\_ERROR will evaluate to false. If there were one or more errors then it will have  the following structure. Errors are added to the array in order of being called._

$EZSQL\_ERROR\[0\] = Array

(

     \[query\] => SOME BAD QUERY

     \[error\_str\] => You have an error in your SQL syntax near ‘SOME BAD QUERY' at line 1

)

$EZSQL\_ERROR\[1\] = Array

(

     \[query\] => ANOTHER BAD QUERY

     \[error\_str\] => You have an error in your SQL syntax near ‘ANOTHER BAD QUERY' at line 1

)

$EZSQL\_ERROR\[2\] = Array

(

     \[query\] => THIRD BAD QUERY

     \[error\_str\] => You have an error in your SQL syntax near ‘THIRD BAD QUERY' at line 1

)

_Example 1_
===========

 // Using a custom error function

$db->hide\_errors();

// Make a silly query that will produce an error

$db->query(“INSERT INTO my\_table A BAD QUERY THAT GENERATES AN ERROR”);

// And another one, for good measure

$db->query(“ANOTHER BAD QUERY THAT GENERATES AN ERROR”);

// If the global error array exists at all then we know there was 1 or more **ezsql** errors..

if ( $EZSQL\_ERROR )

{

 // View the errors

 $db->vardump($EZSQL\_ERROR);

}

else

{

 echo “No Errors”;

}

**$db->show\_errors**

$db->show\_errors – turn **ezsql** error output to browser on

**Description**

**$db->show\_errors**( void )

**$db->show\_errors**() turns **ezsql** error output to the browser on. If you have not used the function $db->hide\_errors this function (show\_errors) will have no effect.

**$db->escape**

$db->escape – Format a string correctly in order to stop accidental mal formed queries under all PHP conditions.

**Description**

**$db->escape**( string )

**$db->escape**() makes any string safe to use as a value in a query under all PHP conditions. I.E. if magic quotes are turned on or off. Note: Should not be used by itself to guard against SQL injection attacks. The purpose of this function is to stop accidental mal formed queries.

_Example 1_
===========

 // Escape and assign the value..

 $title = $db->escape(“Justin’s and Amy’s Home Page”);

 // Insert in to the DB..

$db->query(“INSERT INTO pages (title) VALUES (’$title’)”) ;

_Example 2_
===========

 // Assign the value..

 $title = “Justin’s and Amy’s Home Page”;

 // Insert in to the DB and escape at the same time..

$db->query(“INSERT INTO pages (title) VALUES (’”. $db->escape($title).”’)”) ;

**Disk Caching**

**ezsql** has the ability to cache your queries which can make dynamic sites run much faster.

If you want to cache EVERYTHING just do..

$db->use\_disk\_cache = true;

$db->cache\_queries = true;

$db->cache\_timeout = 24;

For full details and more specific options please see:

· mysql/disk\_cache\_example.php

· oracle8\_9/disk\_cache\_example.php