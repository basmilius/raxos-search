<?php
declare(strict_types=1);

use Raxos\DateTime\DateTime;
use Raxos\Search\Query\Token\DateTimeValue;

covers(DateTimeValue::class);

it('preserves the query nodes text representation', function (): void {
    expect((string)new DateTimeValue(DateTime::parse('2026-01-01T12:34:56+00:00')))->toBe('2026-01-01 12:34:56');
});
