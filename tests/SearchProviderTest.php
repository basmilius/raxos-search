<?php
declare(strict_types=1);

use Raxos\Collection\Map;
use Raxos\Search\Error\IllegalSearchException;
use Raxos\Search\SearchProvider;
use RaxosTests\Search\{DeniedSearchProduct, SilentSearchProduct, UnitSearchProduct};
use function RaxosTests\Search\searchUnitContext;

covers(SearchProvider::class);

it('returns no results without models or without matching filter nodes', function (): void {
    searchUnitContext();
    $provider = new SearchProvider();
    expect($provider->search('apple')->toArray())->toBe([]);
    $provider->registerModel(UnitSearchProduct::class);
    $provider->registerModel(UnitSearchProduct::class);
    expect($provider->search('')->toArray())->toBe([]);
});

it('searches text and explicit fields and ranks numeric scores with the selected limit', function (): void {
    searchUnitContext();
    $provider = new SearchProvider();
    $provider->registerModel(UnitSearchProduct::class);
    $text = $provider->search('"apple"');
    expect($text)->toHaveCount(1)->and($text[0]->model->id)->toBe(1)->and($text[0]->score)->toBe(0.0);
    $rank = $provider->search('rank:any', limit: 2);
    expect(array_map(static fn ($result): int => $result->model->id, $rank->toArray()))->toBe([3, 2])
        ->and(array_column($rank->toArray(), 'score'))->toBe([30.0, 20.0]);
    $restricted = $provider->search('rank:any', context: new Map(['group' => 1]));
    expect(array_map(static fn ($result): int => $result->model->id, $restricted->toArray()))->toBe([2, 1]);
});

it('enforces explicit and silent policy denials before returning models', function (bool $silent): void {
    searchUnitContext();
    $provider = new SearchProvider();
    $provider->registerModel($silent ? SilentSearchProduct::class : DeniedSearchProduct::class);
    if ($silent) {
        expect($provider->search('"apple"')->toArray())->toBe([]);
    } else {
        expect(fn () => $provider->search('"apple"'))->toThrow(IllegalSearchException::class, 'Unit denial');
    }
})->with([true, false]);
