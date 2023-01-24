# php-medoo-wrapper

This library extends Medoo's functionality with added methods for safer database interaction, including a SqlInterpolator class for safe variable interpolation and extended fetch methods.

## Installation

You can install the library using composer:

```
composer require itrn0/php-medoo-wrapper
```

## Usage

```php
require __DIR__ . '/vendor/autoload.php';

use Itrn0\MedooWrapper\MedooWrapper;
use Itrn0\SqlInterpolator\SqlInterpolator;

$db = new MedooWrapper([
    'database_type' => 'mysql',
    'database_name' => 'test',
    'server' => 'localhost',
    'username' => 'root',
    'password' => ''
]);

$usernames = ['alice', 'bob'];
$res = $db->query(function (SqlInterpolator $interp) use ($usernames) {
    return <<<SQL
        SELECT * FROM users WHERE id IN ({$interp(...$usernames)})
    SQL;
});
$users = $res->fetchAll();
```

## Fetching Data

You can fetch data using `fetch` and `fetchAll` methods.

```php
// Fetch a single row from the query result set
$data = $db->fetch("SELECT * FROM `users` WHERE `name` = :name", [
    ':name' => 'John',
]);

// Returns an array containing all of the result set rows
$data = $db->fetchAll("SELECT * FROM `users`");
```

## License

This library is licensed under the MIT License.