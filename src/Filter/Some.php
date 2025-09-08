<?php
declare(strict_types=1);

namespace Raxos\Search\Filter;

use Raxos\Database\Contract\QueryInterface;
use Raxos\Database\Orm\Contract\StructureInterface;
use Raxos\Search\{DatabaseQuery, ScoreExpression};
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Contract\{FilterInterface, QueryNodeInterface};
use const Raxos\Database\Query\expr;

/**
 * Class Some
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Filter
 * @since 2.0.0
 */
final readonly class Some implements FilterInterface
{

    /**
     * Some constructor.
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
        public int $weight = 1
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function apply(StructureInterface $structure, Filter $attribute, QueryInterface $query, QueryNodeInterface $searchQuery): ScoreExpression
    {
        /** @var ScoreExpression[] $scoreExpressions */
        $scoreExpressions = [];

        $query->parenthesis(function () use ($structure, $attribute, $query, $searchQuery, &$scoreExpressions): void {
            foreach ($this->filters as $index => $filter) {
                if ($index === 1 && $query instanceof DatabaseQuery) {
                    $query->convertToOr = true;
                }

                $scoreExpressions[] = $filter->apply($structure, $attribute, $query, $searchQuery);
            }
        });

        if ($query instanceof DatabaseQuery) {
            $query->convertToOr = false;
        }

        return new ScoreExpression(
            expression: expr->greatest(...$scoreExpressions),
            weight: $this->weight
        );
    }

}
