# **ezsql**

[![Windows](https://github.com/ezSQL/ezsql/workflows/Windows/badge.svg)](https://github.com/ezSQL/ezsql/actions?query=workflow%3AWindows)
[![Linux](https://github.com/ezSQL/ezsql/workflows/Linux/badge.svg)](https://github.com/ezSQL/ezsql/actions?query=workflow%3ALinux)
[![macOS](https://github.com/ezSQL/ezsql/workflows/macOS/badge.svg)](https://github.com/ezSQL/ezsql/actions?query=workflow%3AmacOS)
[![codecov](https://codecov.io/gh/ezSQL/ezSQL/branch/master/graph/badge.svg)](https://codecov.io/gh/ezSQL/ezSQL)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/aad1f6aaaaa14f60933e75615da900b8)](https://www.codacy.com/app/techno-express/ezsql?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=ezSQL/ezsql&amp;utm_campaign=Badge_Grade)
[![Maintainability](https://api.codeclimate.com/v1/badges/6f6107f25e9de7bf4272/maintainability)](https://codeclimate.com/github/ezSQL/ezsql/maintainability)
[![Total Downloads](https://poser.pugx.org/ezsql/ezsql/downloads)](https://packagist.org/packages/ezsql/ezsql)

***A class to make it very easy to deal with database connections.***
*An universal interchangeable **CRUD** system.*

This is [__Version 5__](https://github.com/ezSQL/ezsql/tree/v5) which will break users of **version 4**.

Mainly by:

- The use of `namespace` in the `global` functions **ezFunctions.php** file.
    Usage of the **global** functions will require the user to begin a `.php` file something like:

    ```php
    use function ezsql\functions\where;
    // Or
    use function ezsql\functions\{
        getInstance,
        selecting,
        inserting,
    };
    ```

- Class properties that was accessible by magic methods `get/set`, now PSR 1 camelCase.
- Renamed `select` of `ez_mysqli` to `dbSelect`.
- Renamed class method and behavior of `selecting` to `select`.
- `selecting`, and new `inserting` methods, can be called without table name, only the other necessary parameters:
    - The table *name* with *prefix*, can be preset/stored with methods `tableSetup(name, prefix), or setTable(name), setPrefix(append)`, if called without presetting, `false` is returned.
    - This **feature** will be added to **all** database *CRUD* access methods , each method name will have an `ing` ending added.
- Removed global functions where `table` name passed in, use functions using preset table names ending with `ing`.
- renamed cleanInput to clean_string
- renamed createCertificate to create_certificate
- added global get_results to return result sets in different formats

[__Version 4__](https://github.com/ezSQL/ezsql/tree/v4) has many modern programming practices in which will break users of version 3.

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
    create_certificate(string $privatekeyFile = certificate.key,
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
* `select(string $table = null, $columnFields = '*', ...$conditions);`
* `create_select(string $newTable, $fromColumns, $oldTable = null, ...$conditions);`
* `select_into(string $newTable, $fromColumns, $oldTable = null, ...$conditions);`
* `update(string $table = null, $keyAndValue, ...$whereConditions);`
* `delete(string $table = null, ...$whereConditions);`
* `replace(string $table = null, $keyAndValue);`
* `insert(string $table = null, $keyAndValue);`
* `create(string $table = null, ...$schemas);`
* `drop(string $table = null);`
* `alter(string $table = null, ...$alteringSchema);`
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
// To allow simple grouping of basic $whereConditions,
// wrap the following around a group of the above comparison
// expressions within the where( ...$whereConditions) clause
grouping( eq(key, value, combiner ), eq(key, value, combiner ) )
// The above will wrap beginning and end grouping in a where statement
// where required to break down your where clause.
```

```php
// Note: The usage of this method will require the user/developer to check
// if `query_string` or `param_array` is valid.
//
// This is really an `private` internal method for other shortcut methods,
// it's made public for `class development` usage only.
//
//
// Supply the the whole `query` string, and placing '?' within, with the same number of arguments in an array.
// It will then determine arguments type, execute, and return results.
query_prepared(string $query_string, array $param_array);
// You will need to call this method to get last successful query result.
// It wll return an object array.
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
$result = $db->select('profile', 'phone', eq('email', $email), between('id', 1, $values));

foreach ($result as $row) {
    echo $row->phone;
}

$result = $db->select('profile', 'name, email',
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

// To get results in `JSON` format
$json = get_results(JSON, $db);
```

#### Example for using prepare statements directly, no shortcut SQL methods used

```php
$db->query_prepared('INSERT INTO profile( name, email, phone) VALUES( ?, ?, ? );', [$user, $address, $number]);

$db->query_prepared('SELECT name, email FROM profile WHERE phone = ? OR id != ?', [$number, 5]);
$result = $db->queryResult(); // the last query that has results are stored in `lastResult` protected property
// Or for results in other formats use the global function, will use global database instance if no `$db` supplied
$result = get_results(/* OBJECT|ARRAY_A|ARRAY_N|JSON */, $db); // Defaults to `OBJECT`

foreach ($result as $row) {
    echo $row->name.' '.$row->email;
}
```

Most of shortcut methods have counter **global** _functions_ available.
They can only be access by beginning your `.php` file like:

```php
use function ezsql\functions\functionBelow;
// Or as here, a complete list.
use function ezsql\functions\{
    database,
    mysqlInstance,
    pgsqlInstance,
    mssqlInstance,
    sqliteInstance,
    pdoInstance,
    tagInstance,
    setInstance,
    getInstance,
    clearInstance,
    get_vendor,
///
    to_string,
    clean_string,
    is_traversal,
    sanitize_path,
    create_certificate,
///
    column,
    primary,
    foreign,
    unique,
    index,
    addColumn,
    dropColumn,
    changingColumn,
///
    eq,
    neq,
    ne,
    lt,
    lte,
    gt,
    gte,
    isNull,
    isNotNull,
    like,
    in,
    notLike,
    notIn,
    between,
    notBetween,
///
    where,
    grouping,
    groupBy,
    having,
    orderBy,
    limit,
    innerJoin,
    leftJoin,
    rightJoin,
    fullJoin,
    union,
    unionAll,
///
    creating,
    deleting,
    dropping,
    replacing,
    selecting,
    inserting,
    altering,
    get_results,
    table_setup,
    set_table,
    set_prefix,
    select_into,
    insert_select,
    create_select,
};
```

For the functions **usage/docs** see [ezFunctions.php](https://github.com/ezSQL/ezsql/blob/v5/lib/ezFunctions.php).

## For Authors and **[Contributors](https://github.com/ezSQL/ezsql/blob/master/CONTRIBUTORS.md)**

## Contributing

Contributions are encouraged and welcome; I am always happy to get feedback or pull requests on Github :) Create [Github Issues](https://github.com/ezSQL/ezsql/issues) for bugs and new features and comment on the ones you are interested in.

## License

**ezsql** is open-sourced software licensed originally under (LGPL-3.0), and the addon parts under (MIT).
