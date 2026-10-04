<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\DateTime\DateTime as DateTimeUtil;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Filter\DateTime;
use Raxos\Search\Query\Token\{DateTimeValue, NumberValue, RangeValue, Word};
use function RaxosTests\Search\searchUnitContext;

covers(DateTime::class);

it('applies inclusive date limits independently or together', function (array $params, array $ids): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new DateTime(modelKey: 'created_at', weight: 5);
    $node = $filter->fromInput('created', new Map($params));
    $score = $filter->apply($structure, new Filter('created', $filter), $query, $node);
    expect(array_column($query->array(), 'id'))->toBe($ids)->and($score->weight)->toBe(5);
})->with([
    [['created_after' => '2026-01-02 12:00:00'], [2, 3]],
    [['created_before' => '2026-01-02 12:00:00'], [1, 2]],
    [['created_after' => '2026-01-02 12:00:00', 'created_before' => '2026-01-02 12:00:00'], [2]],
]);

it('describes both endpoints and rejects invalid date strings or nodes', function (): void {
    $filter = new DateTime();
    expect($filter->fromInput('created', new Map()))->toBeNull()->and($filter->describe('created'))->toBe([
        ['name' => 'created_after', 'type' => 'string', 'format' => 'date-time'], ['name' => 'created_before', 'type' => 'string', 'format' => 'date-time'],
    ]);
    expect(fn() => $filter->fromInput('created', new Map(['created_after' => 'not a date'])))->toThrow(InvalidFilterValueException::class);
    [, $structure, $query] = searchUnitContext();
    expect(fn() => $filter->apply($structure, new Filter('created_at', $filter), $query, new Word('invalid')))->toThrow(InvalidFilterValueException::class);
});

it('rejects non-scalar endpoints and invalid date range nodes before modifying SQL', function (mixed $value): void {
    $filter = new DateTime();
    expect(fn() => $filter->fromInput('created', new Map(['created_after' => $value])))->toThrow(InvalidFilterValueException::class);
})->with([[[]], [new stdClass()]]);

it('rejects empty or non-date range endpoints before adding either bound', function (RangeValue $range): void {
    [, $structure, $query] = searchUnitContext();
    $sql = $query->toSql();
    $filter = new DateTime(modelKey: 'created_at');
    expect(fn() => $filter->apply($structure, new Filter('created', $filter), $query, $range))->toThrow(InvalidFilterValueException::class);
    expect($query->toSql())->toBe($sql);
})->with([
    [new RangeValue(null, null)],
    [new RangeValue(new NumberValue(1), null)],
    [new RangeValue(new DateTimeValue(DateTimeUtil::parse('2026-01-01')), new NumberValue(1))]
]);
