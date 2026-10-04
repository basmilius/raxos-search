<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Filter\Exact;
use Raxos\Search\Query\Token\{NumberValue, Word};
use function RaxosTests\Search\searchUnitContext;

covers(Exact::class);

it('applies the predicate to actual database rows with the configured weight', function (string $value, array $ids): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Exact(modelKey: 'group_id', weight: 7);
    $score = $filter->apply($structure, new Filter('external', $filter), $query, new Word($value));
    expect(array_column($query->array(), 'id'))->toBe($ids)->and($score->weight)->toBe(7);
})->with([['1', [1, 2]], ['2', [3]], ['0', []]]);

it('describes its structured input and distinguishes missing, empty and zero values', function (): void {
    $filter = new Exact();
    expect($filter->fromInput('field', new Map()))->toBeNull()->and($filter->fromInput('field', new Map(['field' => ''])))->toBeNull()
        ->and($filter->fromInput('field', new Map(['field' => '0']))->text)->toBe('0')
        ->and($filter->describe('field'))->toBe([['name' => 'field', 'type' => 'string']]);
});

it('rejects incompatible query nodes before executing SQL', function (): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Exact(modelKey: 'group_id');
    expect(fn() => $filter->apply($structure, new Filter('field', $filter), $query, new NumberValue(1)))->toThrow(InvalidFilterValueException::class);
});


it('rejects compound structured input without PHP conversion warnings', function (): void {
    $filter = new Exact();
    foreach ([[], ['bad'], new stdClass()] as $input) {
        expect(fn() => $filter->fromInput('field', new Map(['field' => $input])))->toThrow(InvalidFilterValueException::class);
    }
});
