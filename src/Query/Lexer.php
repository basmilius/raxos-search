<?php
declare(strict_types=1);

namespace Raxos\Search\Query;

use Raxos\Error\InvalidArgumentException;
use function array_slice;
use function count;
use function ctype_space;
use function implode;
use function mb_check_encoding;
use function mb_str_split;

/**
 * Class Lexer
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query
 * @since 2.0.0
 */
final class Lexer
{

    /**
     * Caches Unicode characters so scanning does not repeatedly split the query text.
     *
     * @var string[]
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    private readonly array $characters;

    /**
     * Bounds character access using the cached Unicode sequence.
     *
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    private readonly int $length;

    /**
     * Tracks the current position without rescanning earlier input.
     *
     * @var int
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private int $position = 0;

    /**
     * Stops scanning after the final available input character.
     *
     * @var bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
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
        if (!mb_check_encoding($query, 'UTF-8')) {
            throw new InvalidArgumentException('Search query must be valid UTF-8.');
        }

        $this->characters = mb_str_split($query, encoding: 'UTF-8');
        $this->length = count($this->characters);
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

            if ($character === '.' && ($this->peekN(2) === '..' || $this->peekN(3) === '...')) {
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

                if ($character === '.' && ($this->peekN(2) === '..' || $this->peekN(3) === '...')) {
                    break;
                }

                $this->position++;
            }

            $lex = implode('', array_slice($this->characters, $position, $this->position - $position));
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
        $this->position += $this->peekN(3) === '...' ? 3 : 2;

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

        return new Token(TokenType::WHITESPACE, implode('', array_slice($this->characters, $position, $this->position - $position)), $position);
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
        return $this->characters[$this->position] ?? '';
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
        return implode('', array_slice($this->characters, $this->position, $length));
    }

}
