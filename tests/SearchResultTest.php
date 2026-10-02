<?php
declare(strict_types=1);

use Raxos\Search\SearchResult;
use RaxosTests\Search\UnitSearchProduct;
use function RaxosTests\Search\searchUnitContext;

covers(SearchResult::class);

it('retains the numeric score and model identity in its JSON payload', function (): void {
    searchUnitContext();
    $model = UnitSearchProduct::single(1);
    $result = new SearchResult(12.5, $model);
    expect($result->jsonSerialize())->toBe(['score' => 12.5, 'model' => $model])
        ->and(json_decode(json_encode($result, JSON_THROW_ON_ERROR), true)['model']['id'])->toBe(1);
});
