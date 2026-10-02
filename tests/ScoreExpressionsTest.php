<?php
declare(strict_types=1);

use Raxos\Search\{ScoreExpression, ScoreExpressions};
use function Raxos\Database\Query\literal;
use function RaxosTests\Search\searchUnitContext;

covers(ScoreExpressions::class);

it('adds independently weighted scores as a single SQL expression', function (): void {
    [$connection] = searchUnitContext();
    $sum = new ScoreExpressions([new ScoreExpression(literal(3), weight: 2), new ScoreExpression(literal(4), weight: 3)]);
    expect($connection->query()->select(['score' => $sum])->single()['score'])->toBe(18);
});
