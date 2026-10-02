<?php
declare(strict_types=1);

use Raxos\Search\Query\Token\Words;

covers(Words::class);

it('preserves the query nodes text representation', function (): void {
    expect((string)new Words(['héllo', 'world']))->toBe('héllo world');
});
