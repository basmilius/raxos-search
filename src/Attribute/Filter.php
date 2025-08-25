<?php
declare(strict_types=1);

namespace Raxos\Search\Attribute;

use Attribute;
use Raxos\Search\Contract\{AttributeInterface, FilterInterface};

/**
 * Class Filter
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Attribute
 * @since 2.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Filter implements AttributeInterface
{

    /**
     * Filter constructor.
     *
     * @param string $property
     * @param FilterInterface $filter
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string $property,
        public FilterInterface $filter
    ) {}

}
