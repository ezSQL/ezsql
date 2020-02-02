# **ezsql**

[![Build Status](https://travis-ci.org/ezSQL/ezsql.svg?branch=master)](https://travis-ci.org/ezSQL/ezsql)
[![Build status](https://ci.appveyor.com/api/projects/status/6s8oqnoxa2i5k04f?svg=true)](https://ci.appveyor.com/project/jv2222/ezsql)
[![codecov](https://codecov.io/gh/ezSQL/ezSQL/branch/master/graph/badge.svg)](https://codecov.io/gh/ezSQL/ezSQL)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/aad1f6aaaaa14f60933e75615da900b8)](https://www.codacy.com/app/techno-express/ezsql?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=ezSQL/ezsql&amp;utm_campaign=Badge_Grade)
[![Maintainability](https://api.codeclimate.com/v1/badges/6f6107f25e9de7bf4272/maintainability)](https://codeclimate.com/github/ezSQL/ezsql/maintainability)
[![Total Downloads](https://poser.pugx.org/jv2222/ezsql/downloads)](https://packagist.org/packages/jv2222/ezsql)

***A class to make it very easy to deal with database connections.***

This is [__version 4__](https://github.com/ezSQL/ezsql/tree/v4) that has many modern programming practices in which will break users of version 3.

[__Version 3__](https://github.com/ezSQL/ezsql/tree/v3) broke version 2.1.7 in one major way, it required *PHP 5.6*. Which drop mysql extension support, other than that, nothing as far using the library was changed, only additional features.

This library has an `Database` class, an combination of the [Factory](https://en.wikipedia.org/wiki/Factory_method_pattern) pattern with an [Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection) container hosting. This library now is following many OOP principles, one in which, the methods properties public access has been removed. This library also following PSR-2, PSR-4, PSR-11 conventions, and mostly PSR-1, that's still an work in progress.

* More Todo...

For an full overview see [documentation Wiki](https://github.com/ezSQL/ezsql/wiki/Documentation), which is not completely finish.

## Installation

    composer require ezsql/ezsql

## Usage

```php
require 'vendor/autoload.php';

// **** is one of mysqli, pgsql, sqlsrv, sqlite3, or Pdo.
use ezsql\Database;

$db = Database::initialize('****', [$dsn_path_user, $password, $database, $other_settings], $optional_tag);

// Is same as:
use ezsql\Config;
use ezsql\Database\ez_****;

$setting = new Config('****', [$dsn_path_user, $password, $database, $other_settings]);

$db = new ez_****($settings);
```

This library will assume the developer is using some sort of IDE with intellisense enabled. The comments/doc-block area will hold any missing documentations. For additional examples see __phpunit__ tests, The tests are fully functional integration tests, meaning the are live database tests, no mocks.

The following has been added since version 2.1.7.

___General Methods___

    to_string($arrays, $separation = ',');
    clean($string);
    create_cache(string $path = null);
    secureSetup(string $key = 'certificate.key',
        string $cert = 'certificate.crt',
        string $ca = 'cacert.pem',
        string $path = '.'._DS
    );
    secureReset();
    createCertificate(string $privatekeyFile = certificate.key,
        string $certificateFile = certificate.crt,
        string $signingFile = certificate.csr,
        string $ssl_path = null, array $details = [commonName => localhost]
    );

___Shortcut Table Methods___

    create(string $table = null, ...$schemas);// $schemas requires... column()
    column(string $column = null, string $type = null, ...$args);
    primary(string $primaryName, ...$primaryKeys);
    index(string $indexName, ...$indexKeys);
    drop(string $table);
Example

```php
// Creates an database table
create('profile',
    // and with database column name, datatype
    // data types are global CONSTANTS
    // SEQUENCE|AUTO is placeholder tag, to be replaced with the proper SQL drivers auto number sequencer word.
    column('id', INTR, 11, AUTO, PRIMARY), // mysqli
    column('name', VARCHAR, 50, notNULL),
    column('email', CHAR, 25, NULLS),
    column('phone', TINYINT)
);
```

---

    innerJoin(string $leftTable = null, string $rightTable = null,
        string $leftColumn = null, string $rightColumn = null, string $tableAs = null, $condition = EQ);

    leftJoin(string $leftTable = null, string $rightTable = null,
        string $leftColumn = null, string $rightColumn = null, string $tableAs = null, $condition = EQ);

    rightJoin(string $leftTable = null, string $rightTable = null,
        string $leftColumn = null, string $rightColumn = null, string $tableAs = null, $condition = EQ);

    fullJoin(string $leftTable = null, string $rightTable = null,
        string $leftColumn = null, string $rightColumn = null, string $tableAs = null, $condition = EQ);
---

```php
prepareOn(); // When activated will use prepare statements for all shortcut SQL Methods calls.
prepareOff(); // When off shortcut SQL Methods calls will use vendors escape routine instead. This is the default behavior.
```

### Shortcut SQL Methods

* `having(...$having);`
* `groupBy($groupBy);`
* `union(string $table = null, $columnFields = '*', ...$conditions);`
* `unionAll(string $table = null, $columnFields = '*', ...$conditions);`
* `orderBy($orderBy, $order);`
* `limit($numberOf, $offset = null)`
* `where( ...$whereConditions);`
* `selecting(string $table = null, $columnFields = '*', ...$conditions);`
* `create_select(string $newTable, $fromColumns, $oldTable = null, ...$conditions);`
* `select_into(string $newTable, $fromColumns, $oldTable = null, ...$conditions);`
* `update(string $table = null, $keyAndValue, ...$whereConditions);`
* `delete(string $table = null, ...$whereConditions);`
* `replace(string $table = null, $keyAndValue);`
* `insert(string $table = null, $keyAndValue);`
* `insert_select(string $toTable = null, $toColumns = '*', $fromTable = null, $fromColumns = '*', ...$conditions);`

```php
// The variadic ...$whereConditions, and ...$conditions parameters,
//  represent the following global functions.
// They are comparison expressions returning an array with the given arguments,
//  the last arguments of _AND, _OR, _NOT, _andNOT will combine expressions
eq('column', $value, _AND), // combine next expression
neq('column', $value, _OR), // will combine next expression again
ne('column', $value), // the default is _AND so will combine next expression
lt('column', $value)
lte('column', $value)
gt('column', $value)
gte('column', $value)
isNull('column')
isNotNull('column')
like('column', '_%?')
notLike('column', '_%?')
in('column', ...$value)
notIn('column', ...$value)
between('column', $value, $value2)
notBetween('column', $value, $value2)
// The above should be used within the where( ...$whereConditions) clause
// $value will protected by either using escape or prepare statement
```

```php
// Supply the the whole query string, and placing '?' within
// With the same number of arguments in an array.
// It will determine arguments type, execute, and return results.
query_prepared(string $query_string, array $param_array);
// Will need to call to get last successful query result, will return an object array
queryResult();
```

#### Example for using prepare statements indirectly, with above shortcut SQL methods

```php
// To get all shortcut SQL methods calls to use prepare statements
$db->prepareOn(); // This needs to be called at least once at instance creation

$values = [];
$values['name'] = $user;
$values['email'] = $address;
$values['phone'] = $number;
$db->insert('profile', $values);
$db->insert('profile', ['name' => 'john john', 'email' => 'john@email', 'phone' => 123456]);

// returns result set given the table name, column fields, and ...conditions
$result = $db->selecting('profile', 'phone', eq('email', $email), between('id', 1, $values));

foreach ($result as $row) {
    echo $row->phone;
}

$result = $db->selecting('profile', 'name, email',
    // Conditionals can also be called, stacked with other functions like:
    //  innerJoin(), leftJoin(), rightJoin(), fullJoin()
    //      as (leftTable, rightTable, leftColumn, rightColumn, tableAs, equal condition),
    //  where( eq( columns, values, _AND ), like( columns, _d ) ),
    //  groupBy( columns ),
    //  having( between( columns, values1, values2 ) ),
    //  orderBy( columns, desc ),
    //  limit( numberOfRecords, offset ),
    //  union(table, columnFields, conditions),
    //  unionAll(table, columnFields, conditions)
    $db->where( eq('phone', $number, _OR), neq('id', 5) ),
    //  another way: where( array(key, operator, value, combine, combineShifted) );
    //  or as strings double spaced: where( "key  operator  value  combine  combineShifted" );
    $db->orderBy('name'),
    $db->limit(1)
);

foreach ($result as $row) {
    echo $row->name.' '.$row->email;
}
```

#### Example for using prepare statements directly, no shortcut SQL methods used

```php
$db->query_prepared('INSERT INTO profile( name, email, phone) VALUES( ?, ?, ? );', [$user, $address, $number]);

$db->query_prepared('SELECT name, email FROM profile WHERE phone = ? OR id != ?', [$number, 5]);
$result = $db->queryResult(); // the last query that has results are stored in `last_result` protected property

foreach ($result as $row) {
    echo $row->name.' '.$row->email;
}
```

## For Authors and **[Contributors](https://github.com/ezSQL/ezsql/blob/master/CONTRIBUTORS.md)**

## Contributing

Contributions are encouraged and welcome; I am always happy to get feedback or pull requests on Github :) Create [Github Issues](https://github.com/ezSQL/ezsql/issues) for bugs and new features and comment on the ones you are interested in.

## License

**ezsql** is open-sourced software licensed originally under (LGPL-3.0), and the addon parts under (MIT).
