<?php
declare(strict_types=1);

namespace RaxosTests\Search;

use Raxos\Database\Orm\Attribute\{Column, PrimaryKey, SoftDelete, Table};
use Raxos\Database\Orm\Model;
use Raxos\Search\Attribute\Filter;
use Raxos\Search\Filter\Exact;
use function Raxos\Database\Query\literal;

#[Table('raxos_test_search_products'), SoftDelete('deleted_at'), Filter('group_id', new Exact())]
final class SearchProduct extends Model
{

    #[PrimaryKey]
    public int $id;

    #[Column]
    public int $group_id;

    #[Column]
    public int $quantity;

    #[Column]
    public ?string $deleted_at;

    public static function getQueryableColumns(array $columns): array
    {
        return [...$columns, self::col('*'), 'quantity' => literal('`raxos_test_search_products`.`quantity` + 1')];
    }
}
