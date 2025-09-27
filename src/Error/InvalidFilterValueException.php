<?php
declare(strict_types=1);

namespace Raxos\Search\Error;

use Raxos\Contract\Search\SearchExceptionInterface;
use Raxos\Error\Exception;

/**
 * Class InvalidFilterValueException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Error
 * @since 2.0.0
 */
final class InvalidFilterValueException extends Exception implements SearchExceptionInterface
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
            'search:invalid_filter_value',
            "Invalid filter value for filter {$this->filterClass}."
        );
    }

}
