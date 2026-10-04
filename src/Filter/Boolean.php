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
use Raxos\Search\Query\Token\Word;
use Raxos\Search\ScoreExpression;
use Stringable;
use function in_array;

/**
 * Class Boolean
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Filter
 * @since 2.2.0
 */
final readonly class Boolean implements FilterInterface, StructuredFilterInterface
{

    /**
     * Boolean constructor.
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
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function apply(
        StructureInterface $structure,
        Filter $attribute,
        QueryInterface $query,
        QueryNodeInterface $searchQuery
    ): ScoreExpression
    {
        if (!($searchQuery instanceof Word)) {
            throw new InvalidFilterValueException(self::class);
        }

        $modelClass = $this->modelClass ?? $structure->class;
        $modelKey = $this->modelKey ?? $attribute->property;

        $query->where(
            $modelClass::col($modelKey),
            in_array($searchQuery->text, ['true', '1'], true)
        );

        return new ScoreExpression(
            expression: Literal::of(0),
            weight: $this->weight
        );
    }

    /**
     * {@inheritdoc}
     *
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

        $value = is_bool($value) ? ($value ? 'true' : 'false') : (string)$value;

        if ($value === '') {
            return null;
        }

        return new Word($value);
    }

    /**
     * {@inheritdoc}
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function describe(string $property): array
    {
        return [['name' => $property, 'type' => 'boolean']];
    }

}
