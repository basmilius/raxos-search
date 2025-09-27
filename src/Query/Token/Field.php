<?php
declare(strict_types=1);

namespace Raxos\Search\Query\Token;

use Raxos\Contract\Search\QueryNodeInterface;
use Stringable;

/**
 * Class Field
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query\Token
 * @since 2.0.0
 */
final readonly class Field implements QueryNodeInterface, Stringable
{

    /**
     * Field constructor.
     *
     * @param string $key
     * @param QueryNodeInterface|null $value
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string $key,
        public ?QueryNodeInterface $value
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __toString(): string
    {
        return "{$this->key}:{$this->value}";
    }

}
