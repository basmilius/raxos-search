<?php
declare(strict_types=1);

namespace Raxos\Search\Contract;

use Raxos\Database\Contract\QueryInterface;
use Raxos\Database\Error\DatabaseException;
use Raxos\Database\Orm\Contract\StructureInterface;
use Raxos\Foundation\Contract\MapInterface;
use Raxos\Search\Error\SearchException;
use Raxos\Search\Policy\PolicyDecision;

/**
 * Interface PolicyInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Contract
 * @since 2.0.0
 */
interface PolicyInterface
{

    /**
     * Apply the policy to the query.
     *
     * @param StructureInterface $structure
     * @param QueryInterface $query
     * @param MapInterface $context
     *
     * @return PolicyDecision
     * @throws DatabaseException
     * @throws SearchException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function apply(StructureInterface $structure, QueryInterface $query, MapInterface $context): PolicyDecision;

}
