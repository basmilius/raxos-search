<?php
declare(strict_types=1);

use Raxos\Search\Query\Token\{Query, Word};

covers(Query::class);

it('joins nodes in order and preserves their debug identity', function (): void {
    $nodes = [new Word('first'), new Word('second')];
    $query = new Query($nodes);
    expect((string)$query)->toBe('first second')->and($query->__debugInfo())->toBe($nodes)->and((string)new Query([]))->toBe('');
});
