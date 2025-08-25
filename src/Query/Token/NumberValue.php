<?php
declare(strict_types=1);

namespace Raxos\Search\Query\Token;

use Raxos\Search\Contract\QueryNodeInterface;
use Stringable;

/**
 * Class NumberValue
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query\Token
 * @since 2.0.0
 */
final readonly class NumberValue implements QueryNodeInterface, Stringable
{

    /**
     * NumberValue constructor.
     *
     * @param float|int $value
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public float|int $value
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }

}
