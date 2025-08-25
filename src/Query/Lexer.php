<?php
declare(strict_types=1);

namespace Raxos\Search\Query;

use function ctype_space;
use function mb_strlen;
use function mb_substr;

/**
 * Class Lexer
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query
 * @since 2.0.0
 */
final class Lexer
{

    private readonly int $length;
    private int $position = 0;

    public bool $eof {
        get => $this->position >= $this->length;
    }

    /**
     * Lexer constructor.
     *
     * @param string $query
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly string $query
    )
    {
        $this->length = mb_strlen($this->query);
    }

    /**
     * Tokenize the search query.
     *
     * @return Token[]
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function tokenize(): array
    {
        $tokens = [];

        while (!$this->eof) {
            $character = $this->peek();

            if (ctype_space($character)) {
                $tokens[] = $this->consumeWhitespace($this->position);
                continue;
            }

            if ($character === ':') {
                $tokens[] = $this->consumeColon($this->position);
                continue;
            }

            if ($character === '.' && $this->peekN(2) === '..') {
                $tokens[] = $this->consumeDots($this->position);
                continue;
            }

            if ($character === '"') {
                $tokens[] = $this->consumeQuoted($this->position);
                continue;
            }

            $position = $this->position;

            while (!$this->eof) {
                $character = $this->peek();

                if (ctype_space($character) || $character === ':' || $character === '"') {
                    break;
                }

                if ($character === '.' && $this->peekN(2) === '..') {
                    break;
                }

                $this->position++;
            }

            $lex = mb_substr($this->query, $position, $this->position - $position);
            $tokens[] = new Token(TokenType::WORD, $lex, $position);
        }

        $tokens[] = new Token(TokenType::EOF, position: $this->position);

        return $tokens;
    }

    /**
     * Consume a colon.
     *
     * @param int $position
     *
     * @return Token
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function consumeColon(int $position): Token
    {
        $this->position++;

        return new Token(TokenType::COLON, ':', $position);
    }

    /**
     * Consume range dots.
     *
     * @param int $position
     *
     * @return Token
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function consumeDots(int $position): Token
    {
        $this->position += 2;

        return new Token(TokenType::DOTS, '..', $position);
    }

    /**
     * Consume a quoted value.
     *
     * @param int $position
     *
     * @return Token
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function consumeQuoted(int $position): Token
    {
        $this->position++;
        $buffer = '';

        while (!$this->eof) {
            $character = $this->peek();
            $this->position++;

            if ($character === '"') {
                break;
            }

            if ($character === '\\' && !$this->eof) {
                $next = $this->peek();
                $this->position++;
                $buffer .= $next;
                continue;
            }

            $buffer .= $character;
        }

        return new Token(TokenType::QUOTED, $buffer, $position);
    }

    /**
     * Consume whitespace.
     *
     * @param int $position
     *
     * @return Token
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function consumeWhitespace(int $position): Token
    {
        while (!$this->eof && ctype_space($this->peek())) {
            $this->position++;
        }

        return new Token(TokenType::WHITESPACE, mb_substr($this->query, $position, $this->position - $position), $position);
    }

    /**
     * Peek to the next character.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function peek(): string
    {
        return $this->query[$this->position];
    }

    /**
     * Peek to the next number of characters.
     *
     * @param int $length
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function peekN(int $length): string
    {
        return mb_substr($this->query, $this->position, $length);
    }

}
