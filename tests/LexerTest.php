<?php
declare(strict_types=1);

use Raxos\Error\InvalidArgumentException;
use Raxos\Search\Query\{Lexer, TokenType};

covers(Lexer::class);

it('preserves codepoint positions and query syntax', function (): void {
    $tokens = new Lexer('café: "héllo" 1..9')->tokenize();
    expect(array_map(static fn (Raxos\Search\Query\Token $token) => $token->type, $tokens))->toBe([
        TokenType::WORD, TokenType::COLON, TokenType::WHITESPACE, TokenType::QUOTED,
        TokenType::WHITESPACE, TokenType::WORD, TokenType::DOTS, TokenType::WORD, TokenType::EOF,
    ]);
    expect(array_map(static fn (Raxos\Search\Query\Token $token) => $token->position, $tokens))->toBe([0, 4, 5, 6, 13, 14, 15, 17, 18]);
    expect($tokens[3]->lexeme)->toBe('héllo');
});

it('tokenizes long Unicode input without losing characters', function (): void {
    $query = str_repeat('é', 65_536);
    $tokens = new Lexer($query)->tokenize();
    expect($tokens[0]->lexeme)->toBe($query);
    expect($tokens[1]->position)->toBe(65_536);
});

it('rejects invalid UTF8 input', function (): void {
    expect(fn () => new Lexer("\xff"))->toThrow(InvalidArgumentException::class);
});

it('unescapes quoted characters and keeps unfinished phrases available for interactive input', function (): void {
    $tokens = new Lexer('"say \\"hello\\"" "unfinished')->tokenize();
    expect($tokens[0]->lexeme)->toBe('say "hello"')->and($tokens[2]->lexeme)->toBe('unfinished');
});
