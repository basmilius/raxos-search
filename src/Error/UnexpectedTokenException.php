<?php
declare(strict_types=1);

namespace Raxos\Search\Error;

use Raxos\Foundation\Error\ExceptionId;
use Raxos\Search\Query\TokenType;

/**
 * Class UnexpectedTokenException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Error
 * @since 2.0.0
 */
final class UnexpectedTokenException extends SearchException
{

    /**
     * UnexpectedTokenException constructor.
     *
     * @param TokenType $type
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly TokenType $type
    )
    {
        parent::__construct(
            ExceptionId::guess(),
            'search:unexpected_token',
            "Unexpected token. Expected token of type {$this->type->name}."
        );
    }

}
