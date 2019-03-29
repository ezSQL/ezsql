# **ezsql**

[![Build Status](https://travis-ci.org/ezSQL/ezSQL.svg?branch=v4)](https://travis-ci.org/ezSQL/ezSQL)
[![Build status](https://ci.appveyor.com/api/projects/status/6s8oqnoxa2i5k04f?svg=true)](https://ci.appveyor.com/project/jv2222/ezsql)
[![codecov](https://codecov.io/gh/ezSQL/ezSQL/branch/v4/graph/badge.svg)](https://codecov.io/gh/ezSQL/ezSQL)
[![Maintainability](https://api.codeclimate.com/v1/badges/0071eb99ab4f5ac5b1f2/maintainability)](https://codeclimate.com/github/ezSQL/ezSQL/maintainability)

***A class to make it very easy to deal with database connections.***

This is [__version 4__](https://github.com/ezSQL/ezSQL/tree/v4) that has many modern programming practices in which will break users of version 3.

[__Version 3__](https://github.com/ezSQL/ezSQL/tree/v3) broke version 2.1.7 in one major way, it required *PHP 5.6*. Which drop mysql extension support, other than that, nothing as far using the library was changed, only additional features.

This library has an `Database` class, an combination of the [Factory](https://en.wikipedia.org/wiki/Factory_method_pattern) pattern with an [Dependency Injection](https://en.wikipedia.org/wiki/Dependency_injection) container hosting. This library now is following many OOP principles, one in which, the methods properties public access has been removed. This library also following PSR-2, PSR-4, and PSR-11 conventions.

* More Todo...

For an full overview see [documentation Wiki](https://github.com/ezSQL/ezSQL/WIKI.md), which is not completely finish.

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

$db->query_prepared($query_string, $param_array);

**For** **[Authors/Contributors](https://github.com/ezsql/ezsql/CONTRIBUTORS.md)**
