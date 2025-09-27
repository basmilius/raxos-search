<?php
declare(strict_types=1);

namespace Raxos\Search\Error;

use Raxos\Contract\Search\SearchExceptionInterface;
use Raxos\Error\Exception;
use Raxos\Search\Policy\PolicyDecision;

/**
 * Class IllegalSearchException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Error
 * @since 2.0.0
 */
final class IllegalSearchException extends Exception implements SearchExceptionInterface
{

    /**
     * IllegalSearchException constructor.
     *
     * @param PolicyDecision $decision
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly PolicyDecision $decision
    )
    {
        parent::__construct(
            'search:illegal_search',
            $this->decision->reason
        );
    }

}
