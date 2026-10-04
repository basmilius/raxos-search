<?php
declare(strict_types=1);

use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Filter\Text;
use Raxos\Search\Query\Token\{NumberValue, Phrase, Word};
use function RaxosTests\Search\searchUnitContext;

covers(Text::class);

it('filters text through bound SQL values and preserves its weight', function (): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Text(modelKey: 'title', weight: 3);
    $score = $filter->apply($structure, new Filter('text', $filter), $query, new Phrase('a'));
    expect(array_column($query->array(), 'id'))->toBe([1, 2])->and($score->weight)->toBe(3);
    [, $structure, $query] = searchUnitContext();
    $filter->apply($structure, new Filter('text', $filter), $query, new Word("' OR 1=1 --"));
    expect($query->array())->toBe([])->and($query->toSql())->not->toContain('OR 1=1');
});

it('rejects numeric nodes instead of casting them into text', function (): void {
    [, $structure, $query] = searchUnitContext();
    $filter = new Text();
    expect(fn() => $filter->apply($structure, new Filter('title', $filter), $query, new NumberValue(1)))->toThrow(InvalidFilterValueException::class);
});
