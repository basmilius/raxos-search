<?php
declare(strict_types=1);

namespace Raxos\Search\Contract;

/**
 * Interface QueryTextNodeInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Contract
 * @since 2.0.0
 */
interface QueryTextNodeInterface
{

    public string $text {
        get;
    }

}
