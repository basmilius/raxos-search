<?php
declare(strict_types=1);

namespace Raxos\Search\Attribute;

use Attribute;

/**
 * Class SelectOption
 *
 * Declares how a model is presented as type-ahead select options (for dropdowns):
 * which column(s) are searched (substring `LIKE`, suited for type-ahead), the
 * ordering, and the result limits.
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Attribute
 * @since 2.2.0
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class SelectOption
{

    /**
     * Matches search keys with LIKE and limits type-ahead results.
     * A null order uses the first search key; a null emptyLimit uses limit
     * for requests without a search term.
     *
     * @param string[] $searchKeys
     * @param string|null $order
     * @param bool $descending
     * @param int $limit
     * @param int|null $emptyLimit
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function __construct(
        public array $searchKeys,
        public ?string $order = null,
        public bool $descending = false,
        public int $limit = 25,
        public ?int $emptyLimit = null
    ) {}

}
