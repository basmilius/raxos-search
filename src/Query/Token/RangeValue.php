<?php
declare(strict_types=1);

namespace Raxos\Search\Query\Token;

use Raxos\Search\Contract\QueryNodeInterface;
use Stringable;
use function implode;

/**
 * Class RangeValue
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query\Token
 * @since 2.0.0
 */
final readonly class RangeValue implements QueryNodeInterface, Stringable
{

    /**
     * RangeValue constructor.
     *
     * @param DateValue|NumberValue|null $from
     * @param DateValue|NumberValue|null $to
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public DateValue|NumberValue|null $from,
        public DateValue|NumberValue|null $to
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __toString(): string
    {
        return implode('..', [$this->from, $this->to]);
    }

}
