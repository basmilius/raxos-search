<?php
declare(strict_types=1);

namespace Raxos\Search\Contract;

use Raxos\Database\Error\DatabaseException;
use Raxos\Foundation\Contract\{ArrayListInterface, MapInterface};
use Raxos\Search\Error\SearchException;
use Raxos\Search\SearchResult;

/**
 * Interface SearchProviderInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Contract
 * @since 2.0.0
 */
interface SearchProviderInterface
{

    /**
     * Registers a model for search.
     *
     * @param string $modelClass
     *
     * @return void
     * @throws DatabaseException
     * @throws SearchException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function registerModel(string $modelClass): void;

    /**
     * Perform a search using the given query.
     *
     * @param string $query
     * @param MapInterface|null $context
     * @param MapInterface|null $filters
     * @param int $limit
     *
     * @return ArrayListInterface<int, SearchResult>
     * @throws DatabaseException
     * @throws SearchException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function search(string $query, ?MapInterface $context = null, ?MapInterface $filters = null, int $limit = 10): ArrayListInterface;

}
