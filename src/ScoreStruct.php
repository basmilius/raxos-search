<?php
declare(strict_types=1);

namespace Raxos\Search;

use Raxos\Database\Contract\{ConnectionInterface, GrammarInterface, QueryInterface, QueryStructInterface};
use Raxos\Database\Query\QueryHelper;

/**
 * Class ScoreStruct
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search
 * @since 2.0.0
 */
readonly class ScoreStruct implements QueryStructInterface
{

    /**
     * ScoreStruct constructor.
     *
     * @param ScoreExpression[] $expressions
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public array $expressions
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function compile(QueryInterface $query, ConnectionInterface $connection, GrammarInterface $grammar): void
    {
        $query->raw("(");

        foreach ($this->expressions as $index => $expression) {
            if ($index > 0) {
                $query->raw(' + ');
            }

            QueryHelper::value($query, $expression);
        }

        $query->raw(')');
    }

}
