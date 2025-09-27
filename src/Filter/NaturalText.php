<?php
declare(strict_types=1);

namespace Raxos\Search\Filter;

use Raxos\Contract\Database\Orm\StructureInterface;
use Raxos\Contract\Database\Query\QueryInterface;
use Raxos\Contract\Search\{FilterInterface, QueryNodeInterface, QueryTextNodeInterface};
use Raxos\Database\Orm\Model;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\ScoreExpression;
use function array_map;
use const Raxos\Database\Query\expr;

/**
 * Class NaturalText
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Filter
 * @since 2.0.0
 */
final readonly class NaturalText implements FilterInterface
{

    /**
     * NaturalText constructor.
     *
     * @param string[] $keys
     * @param bool $booleanMode
     * @param bool $queryExpansion
     * @param class-string<Model>|null $modelClass
     * @param string|null $modelKey
     * @param int $weight
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public array $keys = [],
        public bool $booleanMode = false,
        public bool $queryExpansion = false,
        public ?string $modelClass = null,
        public ?string $modelKey = null,
        public int $weight = 3
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

        $keys = array_map(static fn(string $field) => $modelClass::col($field), $this->keys);

        if (empty($keys)) {
            $keys[] = $modelClass::col($modelKey);
        }

        $query->where(expr->matchAgainst(
            $keys,
            $searchQuery->text,
            $this->booleanMode,
            $this->queryExpansion
        ));

        return new ScoreExpression(
            expression: expr->matchAgainst(
                $keys,
                $searchQuery->text,
                $this->booleanMode,
                $this->queryExpansion
            ),
            weight: $this->weight
        );
    }

}
