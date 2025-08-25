<?php
declare(strict_types=1);

namespace Raxos\Search\Enum;

/**
 * Class PolicyVerdict
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search\Enum
 * @since 2.0.0
 */
enum PolicyVerdict
{
    case ALLOW;
    case DENY;
    case DENY_SILENT;
}
