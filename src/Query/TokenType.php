<?php
declare(strict_types=1);

namespace Raxos\Search\Query;

/**
 * Enum TokenType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Query
 * @since 2.0.0
 */
enum TokenType
{
    case COLON;
    case DOTS;
    case EOF;
    case QUOTED;
    case WHITESPACE;
    case WORD;
}
