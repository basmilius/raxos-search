<?php
declare(strict_types=1);

namespace Raxos\Search\Error;

use Raxos\Foundation\Error\ExceptionId;
use ReflectionException;

/**
 * Class ReflectionFailedException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Error
 * @since 2.0.0
 */
final class ReflectionFailedException extends SearchException
{

    /**
     * ReflectionFailedException constructor.
     *
     * @param ReflectionException $err
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(ReflectionException $err)
    {
        parent::__construct(
            ExceptionId::guess(),
            'search:reflection_failed',
            'Reflection failed.',
            $err
        );
    }

}
