<?php
declare(strict_types=1);

use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Filter\NaturalText;
use Raxos\Search\Query\Token\{NumberValue, Phrase};
use function RaxosTests\Search\searchUnitContext;

covers(NaturalText::class);

it('compiles fulltext matching with explicit keys or the attribute column fallback', function (array $keys, bool $boolean): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new NaturalText($keys, $boolean, modelKey: 'title', weight: 4);
    $score = $filter->apply($structure, new Filter('text', $filter), $query, new Phrase('apple'));
    expect(strtolower($query->toSql()))->toContain('match', 'against')->and($score->weight)->toBe(4);
    expect(fn () => $filter->apply($structure, new Filter('text', $filter), $query, new NumberValue(1)))->toThrow(InvalidFilterValueException::class);
})->with([[[], false], [['title'], true]]);

it('executes fulltext matches and weighted scores on both supported native engines', function (string $driver, bool $boolean, bool $expansion): void {
    $variable = $driver === 'mysql' ? 'RAXOS_MYSQL_DSN' : 'RAXOS_MARIADB_DSN';
    $dsn = getenv($variable);
    if (!$dsn) {
        $this->markTestSkipped($variable.' is not configured.');
    }
    $class = $driver === 'mysql' ? Raxos\Database\Connection\MySql::class : Raxos\Database\Connection\MariaDb::class;
    $connection = new $class($dsn, getenv('RAXOS_MYSQL_USER') ?: 'root', getenv('RAXOS_MYSQL_PASSWORD') ?: '');
    $connection->connect();
    Raxos\Database\Db::register($connection);
    $connection->execute('DROP TABLE IF EXISTS raxos_unit_fulltext');
    try {
        $connection->execute('CREATE TABLE raxos_unit_fulltext (id INTEGER PRIMARY KEY,title TEXT NOT NULL,FULLTEXT(title)) ENGINE=InnoDB');
        $connection->execute("INSERT INTO raxos_unit_fulltext VALUES (1,'apple orchard'),(2,'banana field'),(3,'cherry garden')");
        $model = RaxosTests\Search\UnitFullText::class;
        $structure = Raxos\Database\Orm\Structure\StructureGenerator::for($model);
        $filter = new NaturalText(['title'], $boolean, $expansion, weight: 4);
        $query = $connection->query()->select(['id'])->from($model::table());
        $score = $filter->apply($structure, new Filter('text', $filter), $query, new Phrase('apple'));
        $row = $query->select(['score' => $score])->orderBy('score DESC')->single();
        expect($row['id'])->toBe(1)->and((float)$row['score'])->toBeGreaterThan(0)->and($score->weight)->toBe(4);
        $none = $connection->query()->select(['id'])->from($model::table());
        $filter->apply($structure, new Filter('text', $filter), $none, new Phrase('unfindableword'));
        expect($none->array())->toBe([]);
    } finally {
        $connection->execute('DROP TABLE IF EXISTS raxos_unit_fulltext');
    }
})->with(['mysql', 'mariadb'])->with([[false, false], [true, false], [false, true]]);
