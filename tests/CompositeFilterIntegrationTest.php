<?php
declare(strict_types=1);

use Raxos\Database\Connection\MariaDb;
use Raxos\Database\Connection\MySql;
use Raxos\Database\Connection\SQLite;
use Raxos\Database\Db;
use Raxos\Database\Orm\Structure\StructureGenerator;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\DatabaseQuery;
use Raxos\Search\Filter\Every;
use Raxos\Search\Filter\Exact;
use Raxos\Search\Filter\Some;
use Raxos\Search\Query\Token\Word;
use RaxosTests\Search\ConstantScoreFilter;
use RaxosTests\Search\SearchProduct;

covers(Every::class, Some::class);

function compositeFilterConnection(string $driver): SQLite|MySql|MariaDb
{
    if ($driver === 'sqlite') {
        $connection = SQLite::createFromInMemory();
    } else {
        $name = $driver === 'mysql' ? 'RAXOS_MYSQL_DSN' : 'RAXOS_MARIADB_DSN';

        if (!getenv($name)) {
            test()->markTestSkipped($name . ' is not configured.');
        }
        $class = $driver === 'mysql' ? MySql::class : MariaDb::class;
        $connection = new $class(getenv($name), getenv('RAXOS_MYSQL_USER') ?: 'root', getenv('RAXOS_MYSQL_PASSWORD') ?: '');
    }
    $connection->connect();
    Db::register($connection);

    return $connection;
}

it('executes weighted conjunction and disjunction scores on every driver', function (string $driver, bool $every): void {
    $connection = compositeFilterConnection($driver);
    $structure = StructureGenerator::for(SearchProduct::class);
    $filters = [new ConstantScoreFilter(2, 4), new ConstantScoreFilter(5, 3)];
    $filter = $every ? new Every($filters, weight: 2) : new Some($filters, weight: 2);
    $query = new DatabaseQuery($connection);
    $score = $filter->apply($structure, new Filter('score', $filter), $query, new Word('x'));
    $row = $connection->query()->select(['score' => $score])->single();
    expect((int)$row['score'])->toBe($every ? 16 : 30);
})->with(['sqlite', 'mysql', 'mariadb'])->with([true, false]);

it('keeps nested OR and AND groups inside the soft-delete boundary on every driver', function (string $driver): void {
    $connection = compositeFilterConnection($driver);
    $table = 'raxos_test_search_products';
    $connection->execute('DROP TABLE IF EXISTS ' . $table);

    try {
        $connection->execute('CREATE TABLE ' . $table . ' (id INTEGER PRIMARY KEY, group_id INTEGER, quantity INTEGER, deleted_at VARCHAR(30) NULL)');
        $connection->execute("INSERT INTO $table VALUES (1,1,10,NULL),(2,2,20,NULL),(3,1,30,NULL),(4,2,40,'2026-01-01')");
        $structure = StructureGenerator::for(SearchProduct::class);
        $filter = new Some([new Exact(modelKey: 'id'), new Every([new Exact(modelKey: 'group_id'), new Exact(modelKey: 'id')])]);
        $query = new DatabaseQuery($connection)->select()->from($table)->withModel(SearchProduct::class)->orderBy('id');
        $filter->apply($structure, new Filter('nested', $filter), $query, new Word('4'));
        expect($query->array())->toBe([]);
        $filter = new Some([new Exact(modelKey: 'id'), new Every([new Exact(modelKey: 'group_id'), new Exact(modelKey: 'id')])]);
        $query = new DatabaseQuery($connection)->select()->from($table)->withModel(SearchProduct::class)->orderBy('id');
        $filter->apply($structure, new Filter('nested', $filter), $query, new Word('2'));
        expect(array_map(static fn(SearchProduct $product): int => $product->id, $query->array()))->toBe([2]);
    } finally {
        $connection->execute('DROP TABLE IF EXISTS ' . $table);
    }
})->with(['sqlite', 'mysql', 'mariadb']);
