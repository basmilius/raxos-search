<?php
declare(strict_types=1);

use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Filter\Enum;
use Raxos\Search\Filter\Every;
use Raxos\Search\Filter\Exact;
use Raxos\Search\Filter\Some;
use Raxos\Search\Query\Token\Word;
use RaxosTests\Search\UnitSearchState;
use function RaxosTests\Search\searchUnitContext;

covers(Every::class);

it('combines child filters with AND and returns the weakest score', function (): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Every([new Exact(modelKey: 'id'), new Exact(modelKey: 'id')], weight: 3);
    $score = $filter->apply($structure, new Filter('field', $filter), $query, new Word('2'));
    expect(array_column($query->array(), 'id'))->toBe([2])->and($query->convertToOr)->toBeFalse()->and($score->weight)->toBe(3);
});

it('treats the empty conjunction as true without adding a predicate', function (): void {
    [, $structure, $query] = searchUnitContext();
    $original = $query->toSql();
    $filter = new Every([]);
    $filter->apply($structure, new Filter('field', $filter), $query, new Word('2'));
    expect($query->toSql())->toBe($original);
});

it('preserves an outer OR connector while keeping the inner conjunction', function (): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Some([new Exact(modelKey: 'id'), new Every([new Exact(modelKey: 'group_id'), new Exact(modelKey: 'id')])]);
    $filter->apply($structure, new Filter('field', $filter), $query, new Word('2'));
    expect(array_column($query->array(), 'id'))->toBe([2]);
});

it('restores an enclosing mode when a child throws', function (): void {
    [, $structure, $query] = searchUnitContext();
    $query->convertToOr = true;
    $filter = new Every([new Enum(UnitSearchState::class)]);
    expect(fn() => $filter->apply($structure, new Filter('field', $filter), $query, new Word('invalid')))->toThrow(InvalidFilterValueException::class);
    expect($query->convertToOr)->toBeTrue();
});
