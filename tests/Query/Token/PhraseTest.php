<?php
declare(strict_types=1);

use Raxos\Search\Query\Token\Phrase;

covers(Phrase::class);

it('preserves the query nodes text representation', function (): void {
    expect((string)new Phrase('héllo world'))->toBe('"héllo world"');
});
