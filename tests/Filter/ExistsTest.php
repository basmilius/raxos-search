<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Filter\Exists;
use Raxos\Search\Query\Token\{NumberValue, Word};
use RaxosTests\Search\{UnitSearchState, UnitSearchTag};
use function RaxosTests\Search\searchUnitContext;

covers(Exists::class);

it('uses correlated existence or absence predicates', function (string $value, array $ids): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Exists(UnitSearchTag::class, ['product_id' => 'id']);
    $filter->apply($structure, new Filter('tag', $filter), $query, new Word($value));
    expect(array_column($query->array(), 'id'))->toBe($ids)->and($filter->describe('tag'))->toBe([['name' => 'tag', 'type' => 'boolean']]);
})->with([['true', [1, 2]], ['false', [3]]]);

it('matches related enum values and rejects missing enum cases or nontext nodes', function (): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Exists(UnitSearchTag::class, ['product_id' => 'id'], 'state', UnitSearchState::class);
    $filter->apply($structure, new Filter('tag', $filter), $query, new Word('blue'));
    expect(array_column($query->array(), 'id'))->toBe([1])->and($filter->describe('tag')[0]['enum'])->toBe(['blue', 'red']);
    foreach ([new Word('invalid'), new NumberValue(1)] as $node) {
        expect(fn () => $filter->apply($structure, new Filter('tag', $filter), $query, $node))->toThrow(InvalidFilterValueException::class);
    }
    expect($filter->fromInput('tag', new Map()))->toBeNull()->and($filter->fromInput('tag', new Map(['tag' => ''])))->toBeNull()
        ->and($filter->fromInput('tag', new Map(['tag' => '0']))->text)->toBe('0');
    expect(new Exists(UnitSearchTag::class, [], 'state')->describe('tag'))->toBe([['name' => 'tag', 'type' => 'string']]);
});


it('rejects compound structured input without PHP conversion warnings', function (): void {
    $filter = new Exists(RaxosTests\Search\UnitSearchTag::class, ['product_id' => 'id']);
    foreach ([[], ['bad'], new stdClass()] as $input) {
        expect(fn () => $filter->fromInput('field', new Map(['field' => $input])))->toThrow(InvalidFilterValueException::class);
    }
});


it('retains native boolean input instead of treating false as an empty filter', function (): void {
    $filter = new Exists(RaxosTests\Search\UnitSearchTag::class, ['product_id' => 'id']);
    expect($filter->fromInput('field', new Map(['field' => false]))->text)->toBe('false')
        ->and($filter->fromInput('field', new Map(['field' => true]))->text)->toBe('true');
});
