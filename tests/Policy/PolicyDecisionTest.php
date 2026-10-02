<?php
declare(strict_types=1);

use Raxos\Search\Enum\PolicyVerdict;
use Raxos\Search\Policy\PolicyDecision;

covers(PolicyDecision::class);

it('distinguishes allowed, denied and silently denied decisions without losing reasons', function (): void {
    expect(PolicyDecision::allow()->verdict)->toBe(PolicyVerdict::ALLOW)->and(PolicyDecision::allow()->reason)->toBeNull()
        ->and(PolicyDecision::deny('reason')->verdict)->toBe(PolicyVerdict::DENY)->and(PolicyDecision::deny('reason')->reason)->toBe('reason')
        ->and(PolicyDecision::denySilent('silent')->verdict)->toBe(PolicyVerdict::DENY_SILENT)->and(PolicyDecision::denySilent('silent')->reason)->toBe('silent');
});
