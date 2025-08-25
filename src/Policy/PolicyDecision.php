<?php
declare(strict_types=1);

namespace Raxos\Search\Policy;

use Raxos\Search\Enum\PolicyVerdict;

/**
 * Class PolicyDecision
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Policy
 * @since 2.0.0
 */
final readonly class PolicyDecision
{

    /**
     * PolicyDecision constructor.
     *
     * @param PolicyVerdict $verdict
     * @param string|null $reason
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function __construct(
        public PolicyVerdict $verdict,
        public ?string $reason = null
    ) {}

    /**
     * Allow the search.
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public static function allow(): self
    {
        return new self(PolicyVerdict::ALLOW);
    }

    /**
     * Deny the search with a reason.
     *
     * @param string $reason
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public static function deny(string $reason): self
    {
        return new self(PolicyVerdict::DENY, $reason);
    }

    /**
     * Deny the search silently and with a reason.
     *
     * @param string $reason
     *
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public static function denySilent(string $reason): self
    {
        return new self(PolicyVerdict::DENY_SILENT, $reason);
    }

}
