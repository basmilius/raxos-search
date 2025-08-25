<?php
declare(strict_types=1);

namespace Raxos\Search;

use JsonSerializable;
use Raxos\Database\Orm\Model;

/**
 * Class SearchResult
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search
 * @since 2.0.0
 */
final readonly class SearchResult implements JsonSerializable
{

    /**
     * SearchResult constructor.
     *
     * @param float $score
     * @param Model $model
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(
        public float $score,
        public Model $model
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function jsonSerialize(): array
    {
        return [
            'score' => $this->score,
            'model' => $this->model
        ];
    }

}
