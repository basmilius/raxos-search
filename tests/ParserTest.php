<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\Search\Error\UnexpectedTokenException;
use Raxos\Search\Filter\Exact;
use Raxos\Search\Query\{Lexer, Parser};
use Raxos\Search\Query\Token\{Field, NumberValue, Phrase, RangeValue, Words};

covers(Parser::class);

it('parses signed numeric ranges with open and closed endpoints', function (string $input, int|float|null $from, int|float|null $to): void {
    $query = new Parser(new Lexer($input)->tokenize())->parse();
    $field = $query->nodes[0];
    expect($field)->toBeInstanceOf(Field::class)->and($field->key)->toBe('price')
        ->and($field->value)->toBeInstanceOf(RangeValue::class)
        ->and($field->value->from?->value)->toBe($from)->and($field->value->to?->value)->toBe($to);
})->with([['price:1..9', 1, 9], ['price:..9', null, 9], ['price:-2..', -2, null], ['PRICE:1.5..3.5', 1.5, 3.5]]);

it('normalizes quoted text, scalar fields and empty queries', function (): void {
    $query = new Parser(new Lexer('"héllo world" count:0')->tokenize())->parse();
    expect($query->nodes[0])->toBeInstanceOf(Field::class)->and($query->nodes[0]->value)->toBeInstanceOf(NumberValue::class)
        ->and($query->nodes[0]->value->value)->toBe(0)
        ->and($query->nodes[1])->toBeInstanceOf(Phrase::class)->and($query->nodes[1]->text)->toBe('héllo world')
        ->and(new Parser(new Lexer('   ')->tokenize())->parse()->nodes)->toBe([]);
});

it('keeps zero-valued structured filter inputs and omits missing or empty ones', function (): void {
    $filter = new Exact();
    expect($filter->fromInput('key', new Map(['key' => '0']))->text)->toBe('0')
        ->and($filter->fromInput('key', new Map(['key' => ''])))->toBeNull()
        ->and($filter->fromInput('key', new Map()))->toBeNull()
        ->and($filter->describe('key'))->toBe([['name' => 'key', 'type' => 'string']]);
});

it('accepts whitespace before field separators and preserves quoted and multiword field values', function (): void {
    $nodes = new Parser(new Lexer('Title : blue sky next: "hello world" empty:')->tokenize())->parse()->nodes;
    expect($nodes)->toHaveCount(3)->and($nodes[0]->key)->toBe('title')->and($nodes[0]->value)->toBeInstanceOf(Words::class)
        ->and((string)$nodes[0]->value)->toBe('blue sky')->and($nodes[1]->value->text)->toBe('hello world')->and($nodes[2]->value)->toBeNull();
});

it('parses signed numbers and calendar dates with open or closed ranges', function (string $input, ?string $from, ?string $to): void {
    $node = new Parser(new Lexer($input)->tokenize())->parse()->nodes[0]->value;
    expect($node->from === null ? null : (string)$node->from)->toBe($from)->and($node->to === null ? null : (string)$node->to)->toBe($to);
})->with([
    ['created:2026-01-01..2026-01-03', '2026-01-01', '2026-01-03'],
    ['created:..2026-01-03', null, '2026-01-03'], ['quantity:+2.5..+3.5', '2.5', '3.5'],
    ['quantity:1...3', '1', '3'],
]);

it('rejects unexpected syntax instead of consuming it as a valid word', function (string $input): void {
    expect(fn() => new Parser(new Lexer($input)->tokenize())->parse())->toThrow(UnexpectedTokenException::class);
})->with([':', '..']);

it('normalizes free text before and after filters while keeping field values typed', function (): void {
    $nodes = new Parser(new Lexer('hello quantity:2 "wide world" created:2026-01-01')->tokenize())->parse()->nodes;
    expect($nodes)->toHaveCount(3)->and($nodes[0]->value->value)->toBe(2)
        ->and((string)$nodes[1]->value)->toBe('2026-01-01')->and($nodes[2]->text)->toBe('hello wide world');
});
