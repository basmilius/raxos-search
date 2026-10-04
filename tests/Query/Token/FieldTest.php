<?php
declare(strict_types=1);

use Raxos\Search\Query\Token\{Field, Word};

covers(Field::class);

it('preserves the query nodes text representation', function (): void {
    expect((string)new Field('key', new Word('value')))->toBe('key:value');
});
