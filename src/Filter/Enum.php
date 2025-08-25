<?php
declare(strict_types=1);

namespace Raxos\Search\Filter;

use BackedEnum;
use Raxos\Database\Contract\{QueryInterface, StructureInterface};
use Raxos\Database\Orm\Model;
use Raxos\Database\Query\Literal\Literal;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Contract\{FilterInterface, QueryNodeInterface};
use Raxos\Search\Error\InvalidFilterValueException;
use Raxos\Search\Query\Token as T;
use Raxos\Search\ScoreExpression;

/**
 * Class Enum
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Filter
 * @since 2.0.0
 */
final readonly class Enum implements FilterInterface
{

    /**
     * Enum constructor.
     *
     * @param class-string<BackedEnum> $enum
     * @param class-string<Model>|null $modelClass
     * @param string|null $modelKey
     * @param int $weight
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string $enum,
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
        if (!($searchQuery instanceof T\Word)) {
            throw new InvalidFilterValueException(self::class);
        }

        $modelClass = $this->modelClass ?? $structure->class;
        $modelKey = $this->modelKey ?? $attribute->property;

        $query->where(
            $modelClass::col($modelKey),
            $searchQuery->text
        );

        return new ScoreExpression(
            expression: Literal::of(0),
            weight: $this->weight
        );
    }

}
