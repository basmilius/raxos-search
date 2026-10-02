<?php
declare(strict_types=1);

use Raxos\Search\Attribute\Filter;
use Raxos\Search\Filter\Every;
use Raxos\Search\Query\Token\Word;
use function RaxosTests\Search\searchUnitContext;

covers(Every::class);

it('reports the currently unsupported composition before modifying the query', function (): void {
    [, $structure, $query] = searchUnitContext();
    $sql = $query->toSql();
    $filter = new Every([]);
    expect(fn () => $filter->apply($structure, new Filter('field', $filter), $query, new Word('value')))->toThrow(RuntimeException::class, 'Not implemented.');
    expect($query->toSql())->toBe($sql);
});
