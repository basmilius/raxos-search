<?php
declare(strict_types=1);

use Raxos\Search\DatabaseQuery;
use RaxosTests\Search\UnitSearchProduct;
use function RaxosTests\Search\searchUnitContext;

covers(DatabaseQuery::class);

it('adopts the original query without modifying it and switches only requested predicates to OR', function (): void {
    [, , $original] = searchUnitContext();
    $sql = $original->toSql();
    $query = new DatabaseQuery($original);
    $query->where(UnitSearchProduct::col('id'), 1);
    $query->convertToOr = true;
    $query->where(UnitSearchProduct::col('id'), 3);
    expect(array_column($query->array(), 'id'))->toBe([1, 3])->and($original->toSql())->toBe($sql);
    $query->convertToOr = false;
    expect($query->convertToOr)->toBeFalse();
});
