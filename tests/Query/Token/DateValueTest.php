<?php
declare(strict_types=1);

use Raxos\DateTime\Date;
use Raxos\Search\Query\Token\DateValue;

covers(DateValue::class);

it('preserves the query nodes text representation', function (): void {
    expect((string)new DateValue(Date::parse('2026-01-01')))->toBe('2026-01-01');
});
