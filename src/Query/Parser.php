<?php
declare(strict_types=1);

namespace Raxos\Search\Query;

use Raxos\DateTime\Date;
use Raxos\Search\Contract\QueryNodeInterface;
use Raxos\Search\Error\{InvalidRangeEndpointException, UnexpectedTokenException};
use Raxos\Search\Query\Token as T;
use Raxos\Search\Query\Token\Query;
use function array_map;
use function count;
use function implode;
use function mb_strtolower;
use function preg_match;
use function str_contains;

/**
 * Class Parser
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query
 * @since 2.0.0
 */
final class Parser
{

    private int $position = 0;

    /**
     * Parser constructor.
     *
     * @param Token[] $tokens
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly array $tokens
    ) {}

    /**
     * Parses the node into the search query structure.
     *
     * @return Query
     * @throws InvalidRangeEndpointException
     * @throws UnexpectedTokenException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function parse(): T\Query
    {
        $nodes = [];

        while (!$this->match(TokenType::EOF)) {
            $this->skipWhitespace();

            if ($this->peekIs(TokenType::EOF)) {
                break;
            }

            $nodes[] = $this->parsePart();

            $this->skipWhitespace();
        }

        $normalized = [];
        $text = [];

        foreach ($nodes as $node) {
            if ($node instanceof T\Phrase || $node instanceof T\Word || $node instanceof T\Words) {
                $text[] = $node;
            } else {
                $normalized[] = $node;
            }
        }

        if (!empty($text)) {
            $normalized[] = new T\Phrase(implode(' ', array_map(\strval(...), $text)));
        }

        return new T\Query($normalized);
    }

    /**
     * Returns a single word or multiple words based on the contents.
     *
     * @param string[] $words
     *
     * @return QueryNodeInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function coerceWordsOrScalar(array $words): QueryNodeInterface
    {
        if (count($words) === 1) {
            $word = $words[0];

            if ($this->looksLikeNumber($word)) {
                return new T\NumberValue(str_contains($word, '.') ? (float)$word : (int)$word);
            }

            if ($this->looksLikeDate($word)) {
                return new T\DateValue(Date::parse($word));
            }

            return new T\Word($word);
        }

        return new T\Words($words);
    }

    /**
     * Consume a token with the given type.
     *
     * @param TokenType $type
     *
     * @return Token
     * @throws UnexpectedTokenException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function consume(TokenType $type): Token
    {
        if (!$this->peekIs($type)) {
            throw new UnexpectedTokenException($type);
        }

        return $this->tokens[$this->position++];
    }

    /**
     * Checks whether the value of a word is eligible for a range endpoint start.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function isEndpointStart(): bool
    {
        if ($this->peekIs(TokenType::WORD)) {
            $value = $this->peek()->lexeme;

            return $this->looksLikeNumber($value) || $this->looksLikeDate($value);
        }

        return false;
    }

    /**
     * Returns TRUE if the given value looks like a date.
     *
     * @param string $value
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function looksLikeDate(string $value): bool
    {
        return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $value);
    }

    /**
     * Returns TRUE if the given value looks like a number. Either integer or float.
     *
     * @param string $value
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function looksLikeNumber(string $value): bool
    {
        return (bool)preg_match('/^[+-]?\d+(?:\.\d+)?$/', $value);
    }

    /**
     * Match the token with the given type and move on to the next one.
     *
     * @param TokenType $type
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function match(TokenType $type): bool
    {
        if ($this->peekIs($type)) {
            $this->position++;

            return true;
        }

        return false;
    }

    /**
     * Parses a range endpoint.
     *
     * @return QueryNodeInterface
     * @throws InvalidRangeEndpointException
     * @throws UnexpectedTokenException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function parseEndpoint(): QueryNodeInterface
    {
        $token = $this->consume(TokenType::WORD);
        $lex = $token->lexeme;

        if ($this->looksLikeNumber($lex)) {
            return new T\NumberValue(str_contains($lex, '.') ? (float)$lex : (int)$lex);
        }

        if ($this->looksLikeDate($lex)) {
            return new T\DateValue(Date::parse($lex));
        }

        throw new InvalidRangeEndpointException($lex, $token->position);
    }

    /**
     * Parses a part.
     *
     * @return QueryNodeInterface
     * @throws InvalidRangeEndpointException
     * @throws UnexpectedTokenException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function parsePart(): QueryNodeInterface
    {
        // Look for a field "key:value"
        if ($this->peekIs(TokenType::WORD) && $this->peekAheadIsColon()) {
            $key = mb_strtolower($this->consume(TokenType::WORD)->lexeme);

            $this->consume(TokenType::COLON);
            $this->skipWhitespace();

            // Range
            if ($this->peekIs(TokenType::DOTS) || $this->isEndpointStart()) {
                $from = null;
                $to = null;

                if ($this->isEndpointStart()) {
                    $from = $this->parseEndpoint();
                    $this->skipWhitespace();
                }

                if ($this->match(TokenType::DOTS)) {
                    $this->skipWhitespace();

                    if ($this->isEndpointStart()) {
                        $to = $this->parseEndpoint();
                        $this->skipWhitespace();
                    }

                    if ($this->peekIs(TokenType::DOTS)) {
                        $this->consume(TokenType::DOTS);
                    }

                    return new T\Field($key, new T\RangeValue($from, $to));
                } else {
                    return new T\Field($key, $from);
                }
            }

            // Quoted value
            if ($this->match(TokenType::QUOTED)) {
                $quoted = $this->previous()->lexeme;

                return new T\Field($key, new T\Phrase($quoted));
            }

            // Single word value
            if ($this->peekIs(TokenType::WORD)) {
                $words = [];

                while ($this->peekIs(TokenType::WORD)) {
                    if ($this->peekAheadIsColon()) {
                        break;
                    }

                    $words[] = $this->consume(TokenType::WORD)->lexeme;
                    $this->skipWhitespace();
                }

                return new T\Field($key, $this->coerceWordsOrScalar($words));
            }

            return new T\Field($key, null);
        }

        // Quoted value
        if ($this->match(TokenType::QUOTED)) {
            return new T\Phrase($this->previous()->lexeme);
        }

        // Single word
        $word = $this->consume(TokenType::WORD)->lexeme;

        return new T\Word($word);
    }

    /**
     * Peeks at the token at the current position.
     *
     * @return Token
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function peek(): Token
    {
        return $this->tokens[$this->position];
    }

    /**
     * Checks if the next token is a colon.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function peekAheadIsColon(): bool
    {
        if (!$this->peekIs(TokenType::WORD)) {
            return false;
        }

        $start = $this->position;
        $start++;

        while ($this->tokens[$start]->type === TokenType::WHITESPACE) {
            $start++;
        }

        return $this->tokens[$start]->type === TokenType::COLON;
    }

    /**
     * Returns TRUE if the token at the current position has the given type.
     *
     * @param TokenType $type
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function peekIs(TokenType $type): bool
    {
        return $this->tokens[$this->position]->type === $type;
    }

    /**
     * Returns the previous token.
     *
     * @return Token
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function previous(): Token
    {
        return $this->tokens[$this->position - 1];
    }

    /**
     * Skip over whitespace.
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function skipWhitespace(): void
    {
        while ($this->peekIs(TokenType::WHITESPACE)) {
            $this->position++;
        }
    }

}
