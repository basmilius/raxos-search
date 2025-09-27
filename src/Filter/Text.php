<?php
declare(strict_types=1);

namespace Raxos\Search\Filter;

use Raxos\Contract\Database\Orm\StructureInterface;
use Raxos\Contract\Database\Query\QueryInterface;
use Raxos\Contract\Search\{FilterInterface, QueryNodeInterface, QueryTextNodeInterface};
use Raxos\Database\Query\Literal\Literal;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\ScoreExpression;

/**
 * Class Text
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Filter
 * @since 2.0.0
 */
final readonly class Text implements FilterInterface
{

    /**
     * Text constructor.
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
        if (!($searchQuery instanceof QueryTextNodeInterface)) {
            throw new InvalidFilterValueException(self::class);
        }

        $modelClass = $this->modelClass ?? $structure->class;
        $modelKey = $this->modelKey ?? $attribute->property;

        $query->where($modelClass::col($modelKey), 'like', "%{$searchQuery->text}%");

        return new ScoreExpression(
            expression: Literal::of(0),
            weight: $this->weight
        );
    }

}
