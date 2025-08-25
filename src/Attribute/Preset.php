<?php
declare(strict_types=1);

namespace Raxos\Search\Attribute;

use Attribute;
use Raxos\Search\Contract\AttributeInterface;

/**
 * Class Preset
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Attribute
 * @since 2.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Preset implements AttributeInterface
{

    /**
     * Preset constructor.
     *
     * @param string $name
     * @param array<string, mixed> $filters
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string $name,
        public array $filters
    ) {}

}
