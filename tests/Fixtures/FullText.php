<?php
declare(strict_types=1);

namespace RaxosTests\Search;

use Raxos\Database\Orm\Attribute\{Column, PrimaryKey, Table};
use Raxos\Database\Orm\Model;

#[Table('raxos_unit_fulltext')]
final class UnitFullText extends Model
{
    #[PrimaryKey] public int $id;
    #[Column] public string $title;
}
