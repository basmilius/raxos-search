<?php
declare(strict_types=1);

namespace Raxos\Search\Filter;

use Raxos\Contract\Database\Orm\StructureInterface;
use Raxos\Contract\Database\Query\QueryInterface;
use Raxos\Contract\Search\FilterInterface;
use Raxos\Contract\Search\QueryNodeInterface;
use Raxos\Database\Query\Expr;
use Raxos\Database\Query\Literal\Literal;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\DatabaseQuery;
use Raxos\Search\ScoreExpression;
use function count;

/**
 * Class Every
 *
 * Combines child predicates with AND and uses the lowest weighted child score.
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
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function apply(
        StructureInterface $structure,
        Filter $attribute,
        QueryInterface $query,
        QueryNodeInterface $searchQuery
    ): ScoreExpression
    {
        if ($this->filters === []) {
            return new ScoreExpression(new Literal(0), weight: $this->weight);
        }

        $scores = [];
        $originalMode = $query instanceof DatabaseQuery && $query->convertToOr;
        $outerOr = $originalMode && $query->isClauseDefined('where');

        try {
            $query->parenthesis(function () use ($structure, $attribute, $query, $searchQuery, $outerOr, &$scores): void {
                $first = true;

                foreach ($this->filters as $filter) {
                    if ($query instanceof DatabaseQuery) {
                        $query->convertToOr = $first && $outerOr;
                    }

                    $first = false;
                    $scores[] = $filter->apply($structure, $attribute, $query, $searchQuery);
                }
            });
        } finally {
            if ($query instanceof DatabaseQuery) {
                $query->convertToOr = $originalMode;
            }
        }

        return new ScoreExpression(count($scores) === 1 ? $scores[0] : Expr::least(...$scores), weight: $this->weight);
    }

}
