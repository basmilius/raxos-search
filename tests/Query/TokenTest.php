<?php
declare(strict_types=1);

use Raxos\Search\Query\{Token, TokenType};

covers(Token::class);

it('retains lexer coordinates and produces a useful token diagnostic', function (): void {
    $token = new Token(TokenType::WORD, 'hello', 12);
    expect($token->lexeme)->toBe('hello')->and($token->position)->toBe(12)->and($token->type)->toBe(TokenType::WORD)
        ->and($token->__debugInfo())->toBe(['token' => 'WORD @ 12 hello']);
});
