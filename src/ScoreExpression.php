<?php
declare(strict_types=1);

namespace Raxos\Search;

use Raxos\Database\Contract\{ConnectionInterface, GrammarInterface, QueryInterface, QueryLiteralInterface, QueryStructInterface};
use Raxos\Database\Query\QueryHelper;

/**
 * Class ScoreExpression
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search
 * @since 2.0.0
 */
final readonly class ScoreExpression implements QueryStructInterface
{

    /**
     * ScoreExpression constructor.
     *
     * @param QueryLiteralInterface|QueryStructInterface $expression
     * @param array $params
     * @param int $weight
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public QueryLiteralInterface|QueryStructInterface $expression,
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
        QueryHelper::value($query, $this->expression);
        $query->raw("* {$this->weight}");
    }

}
