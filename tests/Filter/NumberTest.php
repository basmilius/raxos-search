<?php
declare(strict_types=1);

use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Filter\Number;
use Raxos\Search\Query\Token\{NumberValue, RangeValue, Word};
use function RaxosTests\Search\searchUnitContext;

covers(Number::class);

it('matches exact numbers and open or closed ranges while retaining the configured weight', function (mixed $node, array $ids): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Number(modelKey: 'quantity', weight: 7);
    $score = $filter->apply($structure, new Filter('quantity', $filter), $query, $node);
    expect(array_column($query->array(), 'id'))->toBe($ids)->and($score->weight)->toBe(7);
})->with([
    [new NumberValue(20), [2]],
    [new RangeValue(new NumberValue(10), new NumberValue(20)), [1, 2]],
    [new RangeValue(new NumberValue(20), null), [2, 3]],
    [new RangeValue(null, new NumberValue(20)), [1, 2]],
]);

it('rejects incompatible values and ranges without numeric endpoints', function (): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Number(modelKey: 'quantity');
    foreach ([new Word('invalid'), new RangeValue(null, null)] as $node) {
        expect(fn () => $filter->apply($structure, new Filter('quantity', $filter), $query, $node))->toThrow(InvalidFilterValueException::class);
    }
});


it('executes weighted scores for every numeric range form', function (mixed $node, array $scores): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Number(modelKey: 'quantity', weight: 2);
    $score = $filter->apply($structure, new Filter('quantity', $filter), $query, $node);
    $rows = $query->select(['score' => $score])->array();
    expect($rows)->toHaveCount(count($scores));
    foreach ($rows as $index => $row) {
        expect(abs((float)$row['score'] - $scores[$index]))->toBeLessThan(0.000001);
    }
})->with([
    [new NumberValue(20), [200.0]],
    [new RangeValue(new NumberValue(10), new NumberValue(20)), [120.0, 120.0]],
    [new RangeValue(new NumberValue(20), null), [80.0, 80.0 / 11.0]],
    [new RangeValue(null, new NumberValue(20)), [80.0, 80.0]],
]);

it('rejects non-finite numeric values before emitting SQL', function (float $value): void {
    [, $structure, $query] = RaxosTests\Search\searchUnitContext();
    $sql = $query->toSql();
    $filter = new Raxos\Search\Filter\Number(modelKey: 'quantity');
    expect(fn () => $filter->apply($structure, new Raxos\Search\Attribute\Filter('quantity', $filter), $query, new Raxos\Search\Query\Token\NumberValue($value)))->toThrow(Raxos\Search\Error\InvalidFilterValueException::class);
    expect($query->toSql())->toBe($sql);
})->with([INF, -INF, NAN]);
