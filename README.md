<a href="https://bas.dev">
    <img src="https://bmcdn.nl/assets/branding/logo.svg" alt="Bas Milius" height="48" />
</a>

---

# Raxos Search

Search Raxos ORM models with a query language, reusable filters and access policies.

[Documentation](https://raxos.dev/search/) | [Packagist](https://packagist.org/packages/raxos/search) | [Raxos](https://github.com/basmilius/raxos)

- Field searches, quoted phrases and range expressions.
- Text, exact, numeric, boolean, enum and date/time filters.
- Structured HTTP filters, scoring and model access policies.
- Model pagination built on the database query builder.

## Installation

Requires PHP 8.5 or later. Enable the `ctype`, `mbstring` PHP extensions. Composer checks the remaining package and extension dependencies declared in [composer.json](composer.json).

```sh
composer require "raxos/search:^3.2"
```

## Usage

```php
<?php
declare(strict_types=1);

use Raxos\Database\Connection\SQLite;
use Raxos\Database\Db;
use Raxos\Database\Orm\Attribute\Column;
use Raxos\Database\Orm\Attribute\PrimaryKey;
use Raxos\Database\Orm\Attribute\Table;
use Raxos\Database\Orm\Model;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Filter\Exact;
use Raxos\Search\SearchProvider;

require __DIR__ . '/vendor/autoload.php';

#[Table('products')]
#[Filter('name', new Exact())]
final class Product extends Model
{
    #[PrimaryKey]
    public int $id;

    #[Column]
    public string $name;
}

Db::register(SQLite::createFromInMemory());
Db::execute('CREATE TABLE products (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
Db::query()->insertIntoValues('products', ['id' => 42, 'name' => 'keyboard'])->run();

$search = new SearchProvider();
$search->registerModel(Product::class);

foreach ($search->search('name:keyboard') as $result) {
    echo $result->model->name . PHP_EOL;
}
```

Enable `pdo_sqlite` for this example. Register each model before calling `search()`. Use `applyFilters()` with a parameter map to apply structured filters to an existing query.

`Filter\Every` is currently unsupported. Full-text filters depend on the database driver and its indexes.

## Documentation

- [Query syntax](https://raxos.dev/search/query-syntax)
- [Filters](https://raxos.dev/search/filters)
- [Scoring](https://raxos.dev/search/scoring)
- [Policies](https://raxos.dev/search/policies)

## Testing

Run this library's Pest suite from the Raxos workspace:

```sh
git clone --recurse-submodules https://github.com/basmilius/raxos.git
cd raxos
composer install
vendor/bin/pest --testsuite=search
```

See [Testing Raxos](https://github.com/basmilius/raxos/blob/main/TESTING.md) for PHP extensions, integration services and coverage commands. The library's [Tests workflow](.github/workflows/tests.yml) also runs in GitHub Actions.

## License

[MIT](LICENSE). Copyright (c) 2017 - present Bas Milius.
