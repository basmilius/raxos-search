<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Filter\Boolean;
use Raxos\Search\Query\Token\{NumberValue, Word};
use function RaxosTests\Search\searchUnitContext;

covers(Boolean::class);

it('applies the predicate to actual database rows with the configured weight', function (string $value, array $ids): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Boolean(modelKey: 'enabled', weight: 7);
    $score = $filter->apply($structure, new Filter('external', $filter), $query, new Word($value));
    expect(array_column($query->array(), 'id'))->toBe($ids)->and($score->weight)->toBe(7);
})->with([['true', [1, 3]], ['1', [1, 3]], ['false', [2]], ['0', [2]]]);

it('describes its structured input and distinguishes missing, empty and zero values', function (): void {
    $filter = new Boolean();
    expect($filter->fromInput('field', new Map()))->toBeNull()->and($filter->fromInput('field', new Map(['field' => ''])))->toBeNull()
        ->and($filter->fromInput('field', new Map(['field' => '0']))->text)->toBe('0')
        ->and($filter->describe('field'))->toBe([['name' => 'field', 'type' => 'boolean']]);
});

it('rejects incompatible query nodes before executing SQL', function (): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Boolean(modelKey: 'enabled');
    expect(fn () => $filter->apply($structure, new Filter('field', $filter), $query, new NumberValue(1)))->toThrow(InvalidFilterValueException::class);
});


it('rejects compound structured input without PHP conversion warnings', function (): void {
    $filter = new Boolean();
    foreach ([[], ['bad'], new stdClass()] as $input) {
        expect(fn () => $filter->fromInput('field', new Map(['field' => $input])))->toThrow(InvalidFilterValueException::class);
    }
});


it('retains native boolean input instead of treating false as an empty filter', function (): void {
    $filter = new Boolean();
    expect($filter->fromInput('field', new Map(['field' => false]))->text)->toBe('false')
        ->and($filter->fromInput('field', new Map(['field' => true]))->text)->toBe('true');
});
