<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\Search\Filter\Exact;
use Raxos\Search\Query\Token\{Field, NumberValue, Phrase, RangeValue};
use Raxos\Search\Query\{Lexer, Parser};

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
