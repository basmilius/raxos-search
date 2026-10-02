<?php
declare(strict_types=1);

use Raxos\Search\Query\Token\NumberValue;

covers(NumberValue::class);

it('preserves the query nodes text representation', function (): void {
    expect((string)new NumberValue(-1.5))->toBe('-1.5');
});
