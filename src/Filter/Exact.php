<?php
declare(strict_types=1);

namespace Raxos\Search\Filter;

use Raxos\Contract\Collection\MapInterface;
use Raxos\Contract\Database\Orm\StructureInterface;
use Raxos\Contract\Database\Query\QueryInterface;
use Raxos\Contract\Search\{FilterInterface, QueryNodeInterface, StructuredFilterInterface};
use Raxos\Database\Query\Literal\Literal;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Query\Token as T;
use Raxos\Search\ScoreExpression;

/**
 * Class Exact
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Filter
 * @since 2.2.0
 */
final readonly class Exact implements FilterInterface, StructuredFilterInterface
{

    /**
     * Exact constructor.
     *
     * @param string|null $modelClass
     * @param string|null $modelKey
     * @param int $weight
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function __construct(
        public ?string $modelClass = null,
        public ?string $modelKey = null,
        public int $weight = 1
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function apply(StructureInterface $structure, Filter $attribute, QueryInterface $query, QueryNodeInterface $searchQuery): ScoreExpression
    {
        if (!($searchQuery instanceof T\Word)) {
            throw new InvalidFilterValueException(self::class);
        }

        $modelClass = $this->modelClass ?? $structure->class;
        $modelKey = $this->modelKey ?? $attribute->property;

        $query->where($modelClass::col($modelKey), $searchQuery->text);

        return new ScoreExpression(
            expression: Literal::of(0),
            weight: $this->weight
        );
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function fromInput(string $property, MapInterface $params): ?QueryNodeInterface
    {
        if (!$params->has($property)) {
            return null;
        }

        $value = (string)$params->get($property);

        if ($value === '') {
            return null;
        }

        return new T\Word($value);
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
