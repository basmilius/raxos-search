<?php
declare(strict_types=1);

namespace Raxos\Search\Query\Token;

use Raxos\Search\Contract\{QueryNodeInterface, QueryTextNodeInterface};
use Stringable;
use function addslashes;

/**
 * Class Phrase
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query\Token
 * @since 2.0.0
 */
final readonly class Phrase implements QueryNodeInterface, QueryTextNodeInterface, Stringable
{

    /**
     * Phrase constructor.
     *
     * @param string $text
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string $text
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __toString(): string
    {
        return '"' . addslashes($this->text) . '"';
    }

}
