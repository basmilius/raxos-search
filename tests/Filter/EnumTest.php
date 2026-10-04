<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Filter\Enum;
use Raxos\Search\Query\Token\{NumberValue, Word};
use RaxosTests\Search\{UnitSearchCode, UnitSearchState};
use function RaxosTests\Search\searchUnitContext;

covers(Enum::class);

it('matches backed enum values and describes integer or string input types', function (): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Enum(UnitSearchState::class, modelKey: 'tag', weight: 4);
    $score = $filter->apply($structure, new Filter('state', $filter), $query, new Word('blue'));
    expect(array_column($query->array(), 'id'))->toBe([2])->and($score->weight)->toBe(4)
        ->and($filter->describe('state'))->toBe([['name' => 'state', 'type' => 'string', 'enum' => ['blue', 'red']]])
        ->and(new Enum(UnitSearchCode::class)->describe('code'))->toBe([['name' => 'code', 'type' => 'integer', 'enum' => [1, 2]]]);
});

it('omits missing structured inputs and preserves zero values', function (): void {
    $filter = new Enum(UnitSearchState::class);
    expect($filter->fromInput('state', new Map()))->toBeNull()->and($filter->fromInput('state', new Map(['state' => ''])))->toBeNull()
        ->and($filter->fromInput('state', new Map(['state' => '0']))->text)->toBe('0');
});

it('rejects unknown values and wrong node types before adding predicates', function (): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Enum(UnitSearchState::class);
    foreach ([new Word('missing'), new NumberValue(1)] as $node) {
        expect(fn() => $filter->apply($structure, new Filter('tag', $filter), $query, $node))->toThrow(InvalidFilterValueException::class);
    }
});


it('rejects compound structured input without PHP conversion warnings', function (): void {
    $filter = new Enum(RaxosTests\Search\UnitSearchState::class);
    foreach ([[], ['bad'], new stdClass()] as $input) {
        expect(fn() => $filter->fromInput('field', new Map(['field' => $input])))->toThrow(InvalidFilterValueException::class);
    }
});
