<?php
declare(strict_types=1);

namespace Raxos\Search;

use Raxos\Contract\Database\{ConnectionInterface, GrammarInterface};
use Raxos\Contract\Database\Query\{QueryExpressionInterface, QueryInterface};

/**
 * Class ScoreExpressions
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search
 * @since 2.0.0
 */
readonly class ScoreExpressions implements QueryExpressionInterface
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
        $query->compileMultiple($this->expressions, ' + ');
        $query->raw(')');
    }

}
