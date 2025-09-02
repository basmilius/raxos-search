<?php
declare(strict_types=1);

namespace Raxos\Search\Filter;

use Raxos\Database\Contract\QueryInterface;
use Raxos\Database\Orm\Contract\StructureInterface;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Contract\{FilterInterface, QueryNodeInterface};
use Raxos\Search\ScoreExpression;

/**
 * Class DateTimeRange
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Filter
 * @since 2.0.0
 */
final readonly class DateTimeRange implements FilterInterface
{

    /**
     * DateTimeRange constructor.
     *
     * @param string|null $modelClass
     * @param string|null $modelKey
     * @param int $weight
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public ?string $modelClass = null,
        public ?string $modelKey = null,
        public int $weight = 1
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function apply(StructureInterface $structure, Filter $attribute, QueryInterface $query, QueryNodeInterface $searchQuery): ScoreExpression
    {
        // TODO: Implement apply() method.
    }

}
