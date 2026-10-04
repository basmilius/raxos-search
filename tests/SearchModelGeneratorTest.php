<?php
declare(strict_types=1);

use Raxos\Search\Error\ReflectionErrorException;
use Raxos\Search\SearchModelGenerator;
use RaxosTests\Search\UnitSearchProduct;
use function RaxosTests\Search\searchUnitContext;

covers(SearchModelGenerator::class);

it('collects filters by external key and preserves declared policy and preset metadata', function (): void {
    searchUnitContext();
    $model = SearchModelGenerator::generate(UnitSearchProduct::class);
    expect($model->structure->class)->toBe(UnitSearchProduct::class)->and(array_keys($model->filters))->toBe(['q', 'group', 'rank'])
        ->and($model->policies)->toHaveCount(1)->and($model->presets[0]->name)->toBe('first')->and($model->presets[0]->filters)->toBe(['group' => '1']);
});

it('wraps missing model reflection failures', function (): void {
    expect(fn() => SearchModelGenerator::generate('MissingUnitSearchModel'))->toThrow(ReflectionErrorException::class);
});
