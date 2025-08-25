<?php
declare(strict_types=1);

namespace Raxos\Search\Error;

use Raxos\Foundation\Error\ExceptionId;

/**
 * Class InvalidFilterValueException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Error
 * @since 2.0.0
 */
final class InvalidFilterValueException extends SearchException
{

    /**
     * InvalidFilterValueException constructor.
     *
     * @param string $filterClass
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public readonly string $filterClass
    )
    {
        parent::__construct(
            ExceptionId::guess(),
            'search:invalid_filter_value',
            "Invalid filter value for filter {$this->filterClass}."
        );
    }

}
