<?php
declare(strict_types=1);

namespace Raxos\Search\Filter;

use Raxos\Contract\Database\Orm\StructureInterface;
use Raxos\Contract\Database\Query\QueryInterface;
use Raxos\Contract\Search\FilterInterface;
use Raxos\Contract\Search\QueryNodeInterface;
use Raxos\Database\Query\Expr;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Query\Token as T;
use Raxos\Search\ScoreExpression;
use function abs;
use function max;
use function Raxos\Database\Query\literal;

/**
 * Class Number
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Filter
 * @since 2.0.0
 */
final readonly class Number implements FilterInterface
{
    /**
     * Number constructor.
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
    )
    {
    }

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
        $modelClass = $this->modelClass ?? $structure->class;
        $modelKey = $this->modelKey ?? $attribute->property;
        $col = $modelClass::col($modelKey);
        $columnQuery = $query->connection->query();
        $columnQuery->compile($col);
        $scoreColumn = $columnQuery->toSql();

        if ($searchQuery instanceof T\RangeValue) {
            if (($searchQuery->from !== null && !($searchQuery->from instanceof T\NumberValue))
                || ($searchQuery->to !== null && !($searchQuery->to instanceof T\NumberValue))
                || ($searchQuery->from === null && $searchQuery->to === null)) {
                throw new InvalidFilterValueException(self::class);
            }

            $from = $searchQuery->from instanceof T\NumberValue ? $searchQuery->from->value : null;
            $to = $searchQuery->to instanceof T\NumberValue ? $searchQuery->to->value : null;

            if (($from !== null && !is_finite((float)$from)) || ($to !== null && !is_finite((float)$to))) {
                throw new InvalidFilterValueException(self::class);
            }

            if ($from !== null && $to !== null) {
                $query->where($col, Expr::between($from, $to));

                $midPoint = ($from + $to) / 2;
                $spread = max(1, abs($to - $from));

                return new ScoreExpression(literal(<<<SQL
                    case when {$scoreColumn} between {$from} and {$to}
                        then 50.0 + 20.0 * (1.0 - (1.0 * abs({$scoreColumn} - {$midPoint}) / {$spread}))
                        else 0
                    end
                    SQL
                ), weight: $this->weight);
            }

            if ($from !== null) {
                $query->where($col, '>=', $from);

                return new ScoreExpression(literal(<<<SQL
                    case when {$scoreColumn} >= {$from} then 40.0 * (1.0 / (1.0 + abs({$scoreColumn} - {$from}))) else 0 end
                    SQL
                ), weight: $this->weight);
            }

            $query->where($col, '<=', $to);

            return new ScoreExpression(literal(<<<SQL
                case when {$scoreColumn} <= {$to} then 40 else 0 end
                SQL
            ), weight: $this->weight);
        }

        if ($searchQuery instanceof T\NumberValue) {
            if (!is_finite((float)$searchQuery->value)) {
                throw new InvalidFilterValueException(self::class);
            }

            $query->where($col, $searchQuery->value);

            return new ScoreExpression(literal(<<<SQL
                case when {$scoreColumn} = {$searchQuery->value} then 100 else 0 end
                SQL
            ), weight: $this->weight);
        }

        throw new InvalidFilterValueException(self::class);
    }
}
