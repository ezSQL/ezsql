# **ezsql**

[![Build Status](https://travis-ci.org/ezSQL/ezsql.svg?branch=master)](https://travis-ci.org/ezSQL/ezsql)[![Build status](https://ci.appveyor.com/api/projects/status/6s8oqnoxa2i5k04f?svg=true)](https://ci.appveyor.com/project/jv2222/ezsql)[![codecov](https://codecov.io/gh/ezSQL/ezSQL/branch/master/graph/badge.svg)](https://codecov.io/gh/ezSQL/ezSQL)[![Codacy Badge](https://api.codacy.com/project/badge/Grade/aad1f6aaaaa14f60933e75615da900b8)](https://www.codacy.com/app/techno-express/ezsql?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=ezSQL/ezsql&amp;utm_campaign=Badge_Grade)

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
    securePDO($vendor = null,
        $key = 'certificate.key',
        $cert = 'certificate.crt',
        $ca = 'cacert.pem',
        $path = '.'._DS
    );
    createCertificate(string $privatekeyFile = certificate.key,
        string $certificateFile = certificate.crt,
        string $signingFile = certificate.csr,
        string $ssl_path = null, array $details = [commonName => localhost]
    );

___Shortcut Table Methods___

    create(string $table = null, ...$schemas);// $schemas requires... column()
    column(string $column = null, string $type = null, ...$args);
    drop(string $table);
---

    innerJoin(string $leftTable = null, string $rightTable = null,
        string $leftColumn = null, string $rightColumn = null, $condition = EQ);

    leftJoin(string $leftTable = null, string $rightTable = null,
        string $leftColumn = null, string $rightColumn = null, $condition = EQ);

    rightJoin(string $leftTable = null, string $rightTable = null,
        string $leftColumn = null, string $rightColumn = null, $condition = EQ);

    fullJoin(string $leftTable = null, string $rightTable = null,
        string $leftColumn = null, string $rightColumn = null, $condition = EQ);
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
* `where( ...$whereKeyArray);`
* `selecting(string $table = null, $columnFields = '*', ...$conditions);`
* `create_select(string $newTable, $fromColumns, $oldTable = null, ...$fromWhere);`
* `select_into(string $newTable, $fromColumns, $oldTable = null, ...$fromWhere);`
* `update(string $table = null, $keyAndValue, ...$whereKeys);`
* `delete(string $table = null, ...$whereKeys);`
* `replace(string $table = null, $keyAndValue);`
* `insert(string $table = null, $keyAndValue);`
* `insert_select(string $toTable = null, $toColumns = '*', $fromTable = null, $fromColumns = '*', ...$fromWhere);`

---

    query_prepared(string $query_string, array $param_array);

## For Authors and **[Contributors](https://github.com/ezSQL/ezsql/blob/master/CONTRIBUTORS.md)**

## Contributing

Contributions are encouraged and welcome; I am always happy to get feedback or pull requests on Github :) Create [Github Issues](https://github.com/ezSQL/ezsql/issues) for bugs and new features and comment on the ones you are interested in.

## License

**ezsql** is open-sourced software licensed originally under (LGPL-3.0), and the addon parts under (MIT).
