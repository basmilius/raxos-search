<?php
declare(strict_types=1);

namespace Raxos\Search\Filter;

use Raxos\Database\Contract\QueryInterface;
use Raxos\Database\Orm\Contract\StructureInterface;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Contract\{FilterInterface, QueryNodeInterface};
use Raxos\Search\ScoreExpression;
use RuntimeException;

/**
 * Class Every
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Filter
 * @since 2.0.0
 */
final readonly class Every implements FilterInterface
{

    /**
     * Every constructor.
     *
     * @param FilterInterface[] $filters
     * @param string|null $modelClass
     * @param string|null $modelKey
     * @param int $weight
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public array $filters,
        public ?string $modelClass = null,
        public ?string $modelKey = null,
        public int $weight = 0
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function apply(StructureInterface $structure, Filter $attribute, QueryInterface $query, QueryNodeInterface $searchQuery): ScoreExpression
    {
        throw new RuntimeException('Not implemented.');
    }

}
