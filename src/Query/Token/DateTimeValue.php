<?php
declare(strict_types=1);

namespace Raxos\Search\Query\Token;

use Raxos\Contract\Search\QueryNodeInterface;
use Raxos\DateTime\DateTime;
use Stringable;

/**
 * Class DateTimeValue
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query\Token
 * @since 2.2.0
 */
final readonly class DateTimeValue implements QueryNodeInterface, Stringable
{

    /**
     * DateTimeValue constructor.
     *
     * @param DateTime $value
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function __construct(
        public DateTime $value
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.2.0
     */
    public function __toString(): string
    {
        return $this->value->format('Y-m-d H:i:s');
    }

}
