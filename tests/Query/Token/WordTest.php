<?php
declare(strict_types=1);

use Raxos\Search\Query\Token\Word;

covers(Word::class);

it('preserves the query nodes text representation', function (): void {
    expect((string)new Word('héllo'))->toBe('héllo');
});
