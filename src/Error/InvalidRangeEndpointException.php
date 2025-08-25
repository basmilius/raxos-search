<?php
declare(strict_types=1);

namespace Raxos\Search\Error;

use Raxos\Foundation\Error\ExceptionId;

/**
 * Class InvalidRangeEndpointException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Error
 * @since 2.0.0
 */
final class InvalidRangeEndpointException extends SearchException
{

    /**
     * InvalidRangeEndpointException constructor.
     *
     * @param string $value
     * @param int $position
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public string $value,
        public int $position
    )
    {
        parent::__construct(
            ExceptionId::guess(),
            'search:invalid_range_endpoint',
            "Invalid range endpoint '{$this->value}' at position {$this->position}."
        );
    }

}
