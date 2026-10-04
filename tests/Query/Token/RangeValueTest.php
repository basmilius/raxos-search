<?php
declare(strict_types=1);

use Raxos\Search\Query\Token\{NumberValue, RangeValue};

covers(RangeValue::class);

it('preserves the query nodes text representation', function (): void {
    expect((string)new RangeValue(new NumberValue(1), null))->toBe('1..');
});
