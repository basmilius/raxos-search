<?php
declare(strict_types=1);

namespace Raxos\Search\Filter;

use Raxos\Contract\Collection\MapInterface;
use Raxos\Contract\Database\Orm\StructureInterface;
use Raxos\Contract\Database\Query\QueryInterface;
use Raxos\Contract\Search\FilterInterface;
use Raxos\Contract\Search\QueryNodeInterface;
use Raxos\Contract\Search\StructuredFilterInterface;
use Raxos\Database\Query\Expr;
use Raxos\Database\Query\Literal\Literal;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\DatabaseQuery;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Query\Token as T;
use Raxos\Search\ScoreExpression;
use Stringable;
use function count;
use function is_scalar;


/**
 * Class Some
 *
 * Combines child predicates with OR and uses the highest weighted child score.
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Filter
 * @since 2.0.0
 */
final readonly class Some implements FilterInterface, StructuredFilterInterface
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
    public function apply(
        StructureInterface $structure,
        Filter $attribute,
        QueryInterface $query,
        QueryNodeInterface $searchQuery
    ): ScoreExpression
    {
        if ($this->filters === []) {
            $query->where(false);

            return new ScoreExpression(new Literal(0), weight: $this->weight);
        }

        /** @var ScoreExpression[] $scoreExpressions */
        $scoreExpressions = [];

        $originalMode = $query instanceof DatabaseQuery && $query->convertToOr;
        $outerOr = $originalMode && $query->isClauseDefined('where');

        try {
            $query->parenthesis(function () use ($structure, $attribute, $query, $searchQuery, $outerOr, &$scoreExpressions): void {
                $first = true;

                foreach ($this->filters as $filter) {
                    if ($query instanceof DatabaseQuery) {
                        $query->convertToOr = !$first || $outerOr;
                    }

                    $scoreExpressions[] = $filter->apply($structure, $attribute, $query, $searchQuery);
                    $first = false;
                }
            });
        } finally {
            if ($query instanceof DatabaseQuery) {
                $query->convertToOr = $originalMode;
            }
        }

        return new ScoreExpression(
            expression: count($scoreExpressions) === 1 ? $scoreExpressions[0] : Expr::greatest(...$scoreExpressions),
            weight: $this->weight
        );
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function fromInput(
        string $property,
        MapInterface $params
    ): ?QueryNodeInterface
    {
        if (!$params->has($property)) {
            return null;
        }

        $value = $params->get($property);

        if ($value !== null && !is_scalar($value) && !($value instanceof Stringable)) {
            throw new InvalidFilterValueException(self::class);
        }

        $value = (string)$value;

        if ($value === '') {
            return null;
        }

        return new T\Phrase($value);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function describe(string $property): array
    {
        return [['name' => $property, 'type' => 'string']];
    }
}
