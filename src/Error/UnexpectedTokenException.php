<?php
declare(strict_types=1);

namespace Raxos\Search\Error;

use Raxos\Contract\Search\SearchExceptionInterface;
use Raxos\Error\Exception;
use Raxos\Search\Query\TokenType;

/**
 * Class UnexpectedTokenException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Error
 * @since 2.0.0
 */
final class UnexpectedTokenException extends Exception implements SearchExceptionInterface
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
            'search:unexpected_token',
            "Unexpected token. Expected token of type {$this->type->name}."
        );
    }

}
