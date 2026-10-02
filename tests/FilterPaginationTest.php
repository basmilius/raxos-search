<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\Contract\Database\Query\QueryInterface;
use Raxos\Database\Connection\{MariaDb, MySql, SQLite};
use Raxos\Database\Db;
use Raxos\Database\Orm\ModelArrayList;
use Raxos\Search\SearchProvider;
use RaxosTests\Search\SearchProduct;

dataset('search databases', ['sqlite' => 'sqlite', 'mysql' => 'mysql', 'mariadb' => 'mariadb']);

function searchTestConnection(string $driver): MariaDb|MySql|SQLite
{
    if ($driver === 'sqlite') {
        $connection = new SQLite('sqlite::memory:');
    } else {
        $variable = $driver === 'mysql' ? 'RAXOS_MYSQL_DSN' : 'RAXOS_MARIADB_DSN';
        $dsn = getenv($variable);
        if ($dsn === false || $dsn === '') {
            test()->markTestSkipped("{$variable} is not configured.");
        }
        $class = $driver === 'mysql' ? MySql::class : MariaDb::class;
        $connection = new $class($dsn, getenv('RAXOS_MYSQL_USER') ?: 'root', getenv('RAXOS_MYSQL_PASSWORD') ?: '');
    }
    Db::register($connection);
    $connection->connect();
    $connection->pdo->exec('DROP TABLE IF EXISTS raxos_test_search_products');
    $connection->pdo->exec('CREATE TABLE raxos_test_search_products (id INTEGER PRIMARY KEY, group_id INTEGER NOT NULL, quantity INTEGER NOT NULL, deleted_at VARCHAR(30) NULL)');
    $connection->pdo->exec("INSERT INTO raxos_test_search_products VALUES (1,1,1,NULL),(2,1,3,NULL),(3,2,5,NULL),(4,1,7,'2026-01-01')");
    return $connection;
}

afterEach(function (): void {
    if (isset($this->connection)) {
        $this->connection->pdo->exec('DROP TABLE IF EXISTS raxos_test_search_products');
    }
});

it('paginates filtered model views through the Passly search and visibility path', function (string $driver): void {
    $this->connection = searchTestConnection($driver);
    $original = SearchProduct::select()->orderBy(SearchProduct::col('id'));
    $sql = $original->toSql();
    $query = (new SearchProvider())->applyFilters($original, SearchProduct::class, new Map());
    $page = $query->paginate(0, 2, static fn(QueryInterface $query, int $offset, int $limit): ModelArrayList => $query->limit($limit, $offset)->arrayList()->makeVisible('quantity'));
    $data = json_decode(json_encode($page, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    expect($page->items)->toBeInstanceOf(ModelArrayList::class)
        ->and($data['items'])->toHaveCount(2)
        ->and($data['items'][0]['id'])->toBe(1)
        ->and($data['items'][0]['quantity'])->toBe(2)
        ->and($data['total'])->toBe(3)
        ->and($data['pages'])->toBe(2)
        ->and($original->toSql())->toBe($sql);
})->with('search databases');

it('combines a structured filter with existing query conditions', function (string $driver): void {
    $this->connection = searchTestConnection($driver);
    $original = SearchProduct::select()->where(SearchProduct::col('id'), '>', 1)->orderBy(SearchProduct::col('id'));
    $query = (new SearchProvider())->applyFilters($original, SearchProduct::class, new Map(['group_id' => '1']));
    $page = $query->paginate(0, 25);
    expect($page->total)->toBe(1)
        ->and($page->items[0]->id)->toBe(2)
        ->and($page->items[0]->quantity)->toBe(4)
        ->and($query->totalCount())->toBe(1);
})->with('search databases');

it('returns a correctly typed empty page for a filter without matches', function (string $driver): void {
    $this->connection = searchTestConnection($driver);
    $query = (new SearchProvider())->applyFilters(SearchProduct::select(), SearchProduct::class, new Map(['group_id' => '99']));
    $page = $query->paginate(0, 25, static fn(QueryInterface $query, int $offset, int $limit): ModelArrayList => $query->limit($limit, $offset)->arrayList()->makeVisible('quantity'));
    expect($page->items)->toBeInstanceOf(ModelArrayList::class)
        ->and($page->items->toArray())->toBe([])
        ->and($page->total)->toBe(0)
        ->and($page->pages)->toBe(0);
})->with('search databases');
