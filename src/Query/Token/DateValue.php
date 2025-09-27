<?php
declare(strict_types=1);

namespace Raxos\Search\Query\Token;

use Raxos\Contract\Search\QueryNodeInterface;
use Raxos\DateTime\Date;
use Stringable;

/**
 * Class DateValue
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query\Token
 * @since 2.0.0
 */
final readonly class DateValue implements QueryNodeInterface, Stringable
{

    /**
     * DateValue constructor.
     *
     * @param Date $value
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public Date $value
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __toString(): string
    {
        return $this->value->toDateString();
    }

}
