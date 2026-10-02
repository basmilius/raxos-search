<?php
declare(strict_types=1);

use Raxos\Search\SearchModel;
use function RaxosTests\Search\searchUnitContext;

covers(SearchModel::class);

it('preserves configured filters, policies and presets in debug metadata', function (): void {
    [, $structure] = searchUnitContext();
    $model = new SearchModel($structure, ['filter'], ['policy'], ['preset']);
    expect($model->structure)->toBe($structure)->and($model->__debugInfo())->toBe(['filters' => ['filter'], 'policies' => ['policy'], 'presets' => ['preset']]);
});
