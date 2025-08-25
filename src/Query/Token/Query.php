<?php
declare(strict_types=1);

namespace Raxos\Search\Query\Token;

use Raxos\Foundation\Contract\DebuggableInterface;
use Raxos\Search\Contract\QueryNodeInterface;
use Stringable;
use function array_map;
use function implode;

/**
 * Class Query
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query\Token
 * @since 2.0.0
 * @internal
 * @private
 */
final readonly class Query implements QueryNodeInterface, DebuggableInterface, Stringable
{

    /**
     * Query constructor.
     *
     * @param QueryNodeInterface[] $nodes
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public array $nodes
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __debugInfo(): array
    {
        return $this->nodes;
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __toString(): string
    {
        return implode(' ', array_map(\strval(...), $this->nodes));
    }

}
