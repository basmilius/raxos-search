<?php
declare(strict_types=1);

namespace Raxos\Search\Query;

use Raxos\Foundation\Contract\DebuggableInterface;

/**
 * Class Token
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query
 * @since 2.0.0
 */
final readonly class Token implements DebuggableInterface
{

    /**
     * Token constructor.
     *
     * @param TokenType $type
     * @param string $lexeme
     * @param int $position
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public TokenType $type,
        public string $lexeme = '',
        public int $position = 0
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __debugInfo(): array
    {
        return [
            'token' => "{$this->type->name} @ {$this->position} {$this->lexeme}"
        ];
    }

}
