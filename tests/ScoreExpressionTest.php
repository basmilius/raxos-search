<?php
declare(strict_types=1);

use Raxos\Search\ScoreExpression;
use function Raxos\Database\Query\literal;
use function RaxosTests\Search\searchUnitContext;

covers(ScoreExpression::class);

it('weights the complete expression according to SQL arithmetic precedence', function (): void {
    [$connection] = searchUnitContext();
    $score = new ScoreExpression(literal('3 + 4'), weight: 2);
    $value = $connection->query()->select(['score' => $score])->single();
    expect($value['score'])->toBe(14)->and($score->weight)->toBe(2);
});
