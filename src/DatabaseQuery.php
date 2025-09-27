<?php
declare(strict_types=1);

namespace Raxos\Search;

use BackedEnum;
use Raxos\Contract\Database\ConnectionInterface;
use Raxos\Contract\Database\Query\{QueryInterface, QueryValueInterface};
use Raxos\Database\Query\Query;
use Stringable;

/**
 * Class DatabaseQuery
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search
 * @since 2.0.0
 * @internal
 * @private
 */
class DatabaseQuery extends Query
{

    public bool $convertToOr = false;

    /**
     * DatabaseQuery constructor.
     *
     * @param ConnectionInterface|QueryInterface $connectionOrQuery
     * @param bool $prepared
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(ConnectionInterface|QueryInterface $connectionOrQuery, bool $prepared = false)
    {
        $connection = $connectionOrQuery instanceof QueryInterface ? $connectionOrQuery->connection : $connectionOrQuery;
        $prepared = $connectionOrQuery instanceof QueryInterface ? $connectionOrQuery->prepared : $prepared;

        parent::__construct($connection, $prepared);

        if ($connectionOrQuery instanceof QueryInterface) {
            $this->merge($connectionOrQuery);
        }
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function where(
        BackedEnum|Stringable|QueryValueInterface|string|int|float|bool|null $lhs = null,
        BackedEnum|Stringable|QueryValueInterface|string|int|float|bool|null $cmp = null,
        BackedEnum|Stringable|QueryValueInterface|string|int|float|bool|null $rhs = null
    ): static
    {
        if ($this->convertToOr) {
            return parent::orWhere($lhs, $cmp, $rhs);
        } else {
            return parent::where($lhs, $cmp, $rhs);
        }
    }

}
