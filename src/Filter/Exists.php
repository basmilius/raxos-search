<?php
declare(strict_types=1);

namespace Raxos\Search\Filter;

use BackedEnum;
use Raxos\Contract\Collection\MapInterface;
use Raxos\Contract\Database\Orm\StructureInterface;
use Raxos\Contract\Database\Query\QueryInterface;
use Raxos\Contract\Search\{FilterInterface, QueryNodeInterface, QueryTextNodeInterface, StructuredFilterInterface};
use Raxos\Database\Db;
use Raxos\Database\Orm\Model;
use Raxos\Database\Query\Literal\Literal;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Query\Token as T;
use Raxos\Search\ScoreExpression;
use function array_map;
use function in_array;
use function is_int;
use function Raxos\Database\Query\literal;

/**
 * Class Exists
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Filter
 * @since 2.2.0
 */
final readonly class Exists implements FilterInterface, StructuredFilterInterface
{

    /**
     * Exists constructor.
     *
     * Without a {@see self::$matchKey} this is a pure existence filter: a boolean
     * input toggles between `EXISTS` / `NOT EXISTS` of the correlated subquery.
     * With a {@see self::$matchKey} the input is matched against that column on the
     * related table (optionally validated against {@see self::$enum}).
     *
     * @param class-string<Model> $relation
     * @param array<string, string> $on relation-column => model-column correlation
     * @param string|null $matchKey
     * @param class-string<BackedEnum>|null $enum
     * @param class-string<Model>|null $modelClass
     * @param string|null $modelKey
     * @param int $weight
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function __construct(
        public string $relation,
        public array $on,
        public ?string $matchKey = null,
        public ?string $enum = null,
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
        $modelClass = $this->modelClass ?? $structure->class;

        $sub = Db::query()
            ->select([literal('1')])
            ->from($this->relation::table());

        foreach ($this->on as $relationColumn => $modelColumn) {
            $sub->where($this->relation::col($relationColumn), $modelClass::col($modelColumn));
        }

        if ($this->matchKey !== null) {
            if (!($searchQuery instanceof QueryTextNodeInterface)) {
                throw new InvalidFilterValueException(self::class);
            }

            $value = $searchQuery->text;

            if ($this->enum !== null) {
                $case = null;

                foreach ($this->enum::cases() as $enumCase) {
                    if ((string)$enumCase->value === $value) {
                        $case = $enumCase;
                        break;
                    }
                }

                if ($case === null) {
                    throw new InvalidFilterValueException(self::class);
                }

                $value = $case->value;
            }

            $sub->where($this->relation::col($this->matchKey), $value);
            $query->whereExists($sub);

            return new ScoreExpression(
                expression: Literal::of(0),
                weight: $this->weight
            );
        }

        $wantsExists = !($searchQuery instanceof T\Word) || in_array($searchQuery->text, ['true', '1'], true);

        if ($wantsExists) {
            $query->whereExists($sub);
        } else {
            $query->whereNotExists($sub);
        }

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
        if ($this->enum !== null) {
            $cases = $this->enum::cases();
            $isInt = $cases !== [] && is_int($cases[0]->value);

            return [['name' => $property, 'type' => $isInt ? 'integer' : 'string', 'enum' => array_map(static fn(BackedEnum $case) => $case->value, $cases)]];
        }

        return [['name' => $property, 'type' => $this->matchKey !== null ? 'string' : 'boolean']];
    }

}
