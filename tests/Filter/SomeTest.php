<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Filter\{Exact, Some};
use Raxos\Search\Query\Token\Word;
use function RaxosTests\Search\searchUnitContext;

covers(Some::class);

it('groups child predicates with OR and restores the original query mode', function (): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Some([new Exact(modelKey: 'id'), new Exact(modelKey: 'group_id')], weight: 3);
    $score = $filter->apply($structure, new Filter('field', $filter), $query, new Word('2'));
    expect(array_column($query->array(), 'id'))->toBe([2, 3])->and($query->convertToOr)->toBeFalse()->and($score->weight)->toBe(3)
        ->and($filter->describe('field'))->toBe([['name' => 'field', 'type' => 'string']]);
    expect($filter->fromInput('field', new Map()))->toBeNull()->and($filter->fromInput('field', new Map(['field' => ''])))->toBeNull()
        ->and($filter->fromInput('field', new Map(['field' => 'value']))->text)->toBe('value');
});

it('restores the query mode even when a child filter fails', function (): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Some([new Exact(modelKey: 'id'), new Raxos\Search\Filter\Enum(RaxosTests\Search\UnitSearchState::class)]);
    expect(fn () => $filter->apply($structure, new Filter('field', $filter), $query, new Word('invalid')))->toThrow(InvalidFilterValueException::class);
    expect($query->convertToOr)->toBeFalse();
});


it('rejects compound structured input without PHP conversion warnings', function (): void {
    $filter = new Some([]);
    foreach ([[], ['bad'], new stdClass()] as $input) {
        expect(fn () => $filter->fromInput('field', new Map(['field' => $input])))->toThrow(InvalidFilterValueException::class);
    }
});


it('preserves an enclosing OR mode and associative child keys', function (): void {
    [, $structure, $query] = searchUnitContext();
    $query->convertToOr = true;
    $filter = new Some(['first' => new Exact(modelKey: 'id'), 'second' => new Exact(modelKey: 'group_id')]);
    $filter->apply($structure, new Filter('field', $filter), $query, new Word('2'));
    expect($query->convertToOr)->toBeTrue()->and(array_column($query->array(), 'id'))->toBe([2, 3]);
});
