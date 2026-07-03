<?php
declare(strict_types=1);

namespace Raxos\Search\Filter;

use Raxos\Contract\Collection\MapInterface;
use Raxos\Contract\Database\Orm\StructureInterface;
use Raxos\Contract\Database\Query\QueryInterface;
use Raxos\Contract\Search\{FilterInterface, QueryNodeInterface, StructuredFilterInterface};
use Raxos\Database\Query\Literal\Literal;
use Raxos\DateTime\DateTime as DateTimeUtil;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Query\Token as T;
use Raxos\Search\ScoreExpression;
use Throwable;

/**
 * Class DateTime
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Filter
 * @since 2.0.0
 */
final readonly class DateTime implements FilterInterface, StructuredFilterInterface
{

    /**
     * DateTime constructor.
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
        if (!($searchQuery instanceof T\RangeValue)) {
            throw new InvalidFilterValueException(self::class);
        }

        $modelClass = $this->modelClass ?? $structure->class;
        $modelKey = $this->modelKey ?? $attribute->property;
        $col = $modelClass::col($modelKey);

        if ($searchQuery->from instanceof T\DateValue || $searchQuery->from instanceof T\DateTimeValue) {
            $query->where($col, '>=', (string)$searchQuery->from);
        }

        if ($searchQuery->to instanceof T\DateValue || $searchQuery->to instanceof T\DateTimeValue) {
            $query->where($col, '<=', (string)$searchQuery->to);
        }

        return new ScoreExpression(
            expression: Literal::of(0),
            weight: $this->weight
        );
    }

    /**
     * {@inheritdoc}
     * @throws InvalidFilterValueException
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function fromInput(string $property, MapInterface $params): ?QueryNodeInterface
    {
        $afterKey = $property . '_after';
        $beforeKey = $property . '_before';

        $after = $params->has($afterKey) ? (string)$params->get($afterKey) : '';
        $before = $params->has($beforeKey) ? (string)$params->get($beforeKey) : '';

        if ($after === '' && $before === '') {
            return null;
        }

        try {
            $from = $after !== '' ? new T\DateTimeValue(DateTimeUtil::parse($after)) : null;
            $to = $before !== '' ? new T\DateTimeValue(DateTimeUtil::parse($before)) : null;
        } catch (Throwable) {
            throw new InvalidFilterValueException(self::class);
        }

        return new T\RangeValue($from, $to);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function describe(string $property): array
    {
        return [
            ['name' => $property . '_after', 'type' => 'string', 'format' => 'date-time'],
            ['name' => $property . '_before', 'type' => 'string', 'format' => 'date-time']
        ];
    }

}
