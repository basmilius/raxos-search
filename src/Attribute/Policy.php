<?php
declare(strict_types=1);

namespace Raxos\Search\Attribute;

use Attribute;
use Raxos\Search\Contract\{AttributeInterface, PolicyInterface};

/**
 * Class Policy
 *
 * @template TPolicy
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Attribute
 * @since 2.0.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Policy implements AttributeInterface
{

    /**
     * Policy constructor.
     *
     * @param PolicyInterface $policy
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public PolicyInterface $policy
    ) {}

}
