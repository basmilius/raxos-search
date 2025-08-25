<?php
declare(strict_types=1);

namespace Raxos\Search\Query\Token;

use Raxos\Search\Contract\{QueryNodeInterface, QueryTextNodeInterface};
use Stringable;
use function implode;

/**
 * Class Words
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query\Token
 * @since 2.0.0
 */
final readonly class Words implements QueryNodeInterface, QueryTextNodeInterface, Stringable
{

    public string $text;

    /**
     * Words constructor.
     *
     * @param string[] $words
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public array $words
    )
    {
        $this->text = implode(' ', $this->words);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __toString(): string
    {
        return implode(' ', $this->words);
    }

}
