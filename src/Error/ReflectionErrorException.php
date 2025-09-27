<?php
declare(strict_types=1);

namespace Raxos\Search\Error;

use Raxos\Contract\Reflection\ReflectionFailedExceptionInterface;
use Raxos\Contract\Search\SearchExceptionInterface;
use Raxos\Error\Exception;
use ReflectionException;

/**
 * Class ReflectionErrorException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Error
 * @since 2.0.0
 */
final class ReflectionErrorException extends Exception implements SearchExceptionInterface, ReflectionFailedExceptionInterface
{

    /**
     * ReflectionErrorException constructor.
     *
     * @param ReflectionException $err
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly ReflectionException $err
    )
    {
        parent::__construct(
            'search:reflection_failed',
            'Reflection failed.',
            previous: $err
        );
    }

}
