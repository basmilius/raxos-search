<?php
declare(strict_types=1);

namespace Raxos\Search;

use Raxos\Database\Orm\Contract\StructureInterface;
use Raxos\Database\Orm\Model;
use Raxos\Foundation\Contract\DebuggableInterface;
use Raxos\Search\Attribute\{Filter, Preset};
use Raxos\Search\Contract\PolicyInterface;

/**
 * Class SearchModel
 *
 * @template TModel of Model
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search
 * @since 2.0.0
 */
final readonly class SearchModel implements DebuggableInterface
{

    /**
     * SearchModel constructor.
     *
     * @param StructureInterface $structure
     * @param array<string, Filter> $filters
     * @param PolicyInterface[] $policies
     * @param Preset[] $presets
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public StructureInterface $structure,
        public array $filters = [],
        public array $policies = [],
        public array $presets = []
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __debugInfo(): array
    {
        return [
            'filters' => $this->filters,
            'policies' => $this->policies,
            'presets' => $this->presets
        ];
    }

}
