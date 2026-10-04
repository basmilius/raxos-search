<?php
declare(strict_types=1);

namespace RaxosTests\Search;

use Raxos\Contract\Database\Orm\StructureInterface;
use Raxos\Contract\Database\Query\QueryInterface;
use Raxos\Contract\Search\FilterInterface;
use Raxos\Contract\Search\QueryNodeInterface;
use Raxos\Database\Query\Literal\Literal;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\ScoreExpression;

final readonly class ConstantScoreFilter implements FilterInterface
{
    public function __construct(public int $score, public int $weight, public ?string $modelClass = null, public ?string $modelKey = null) {}

    public function apply(StructureInterface $structure, Filter $attribute, QueryInterface $query, QueryNodeInterface $searchQuery): ScoreExpression
    {
        $query->where(new Literal(1), 1);

        return new ScoreExpression(new Literal($this->score), weight: $this->weight);
    }
}
