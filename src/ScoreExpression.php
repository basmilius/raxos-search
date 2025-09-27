<?php
declare(strict_types=1);

namespace Raxos\Search;

use Raxos\Contract\Database\{ConnectionInterface, GrammarInterface};
use Raxos\Contract\Database\Query\{QueryExpressionInterface, QueryInterface, QueryLiteralInterface};

/**
 * Class ScoreExpression
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search
 * @since 2.0.0
 */
final readonly class ScoreExpression implements QueryExpressionInterface
{

    /**
     * ScoreExpression constructor.
     *
     * @param QueryLiteralInterface|QueryExpressionInterface $expression
     * @param array $params
     * @param int $weight
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public QueryLiteralInterface|QueryExpressionInterface $expression,
        public array $params = [],
        public int $weight = 1
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function compile(QueryInterface $query, ConnectionInterface $connection, GrammarInterface $grammar): void
    {
        $query->compile($this->expression);
        $query->raw("* {$this->weight}");
    }

}
