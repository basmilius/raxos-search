<?php
declare(strict_types=1);

namespace Raxos\Search\Contract;

use Raxos\Database\Contract\{QueryInterface, StructureInterface};
use Raxos\Database\Error\QueryException;
use Raxos\Database\Orm\Error\StructureException;
use Raxos\Database\Orm\Model;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Error\SearchException;
use Raxos\Search\ScoreExpression;

/**
 * Interface FilterInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Contract
 * @since 2.0.0
 */
interface FilterInterface
{

    /** @var class-string<Model>|null */
    public ?string $modelClass {
        get;
    }

    public ?string $modelKey {
        get;
    }

    public int $weight {
        get;
    }

    /**
     * Adds the filter to the search query.
     *
     * @param StructureInterface $structure
     * @param Filter $attribute
     * @param QueryInterface $query
     * @param QueryNodeInterface $searchQuery
     *
     * @return ScoreExpression
     * @throws QueryException
     * @throws SearchException
     * @throws StructureException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function apply(StructureInterface $structure, Filter $attribute, QueryInterface $query, QueryNodeInterface $searchQuery): ScoreExpression;

}
