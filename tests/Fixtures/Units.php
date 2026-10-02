<?php
declare(strict_types=1);

namespace RaxosTests\Search;

use Raxos\Contract\Collection\MapInterface;
use Raxos\Contract\Database\Orm\StructureInterface;
use Raxos\Contract\Database\Query\QueryInterface;
use Raxos\Contract\Search\{FilterInterface, PolicyInterface, QueryNodeInterface};
use Raxos\Database\Connection\SQLite;
use Raxos\Database\Db;
use Raxos\Database\Orm\Attribute\{Column, PrimaryKey, Table};
use Raxos\Database\Orm\Model;
use Raxos\Database\Orm\Structure\StructureGenerator;
use Raxos\Search\Attribute\{Filter, Policy, Preset};
use Raxos\Search\{DatabaseQuery, ScoreExpression};
use Raxos\Search\Enum\PolicyVerdict;
use Raxos\Search\Filter\{Exact, Text};
use Raxos\Search\Policy\PolicyDecision;

enum UnitSearchState: string
{
    case Blue = 'blue';
    case Red = 'red';
}
enum UnitSearchCode: int
{
    case First = 1;
    case Second = 2;
}

final readonly class UnitScoreFilter implements FilterInterface
{
    public function __construct(public ?string $modelClass = null, public ?string $modelKey = null, public int $weight = 1)
    {
    }
    public function apply(StructureInterface $structure, Filter $attribute, QueryInterface $query, QueryNodeInterface $searchQuery): ScoreExpression
    {
        $query->where($structure->class::col('quantity'), '>', 0);
        return new ScoreExpression($structure->class::col('quantity'));
    }
}

final readonly class UnitPolicy implements PolicyInterface
{
    public function __construct(public PolicyVerdict $verdict = PolicyVerdict::ALLOW)
    {
    }
    public function apply(StructureInterface $structure, QueryInterface $query, MapInterface $context): PolicyDecision
    {
        if ($context->has('group')) {
            $query->where($structure->class::col('group_id'), $context->get('group'));
        }
        return match ($this->verdict) {
            PolicyVerdict::ALLOW => PolicyDecision::allow(),
            PolicyVerdict::DENY => PolicyDecision::deny('Unit denial'),
            PolicyVerdict::DENY_SILENT => PolicyDecision::denySilent('Unit silence'),
        };
    }
}

#[Table('raxos_unit_search')]
#[Filter('q', new Text(modelKey: 'title'))]
#[Filter('group', new Exact(modelKey: 'group_id'))]
#[Filter('rank', new UnitScoreFilter())]
#[Policy(new UnitPolicy())]
#[Preset('first', ['group' => '1'])]
class UnitSearchProduct extends Model
{
    #[PrimaryKey] public int $id;
    #[Column] public string $title;
    #[Column] public int $group_id;
    #[Column] public int $quantity;
    #[Column] public bool $enabled;
    #[Column] public ?string $tag;
    #[Column] public string $created_at;
}

#[Table('raxos_unit_search')]
#[Filter('q', new Text(modelKey: 'title'))]
#[Policy(new UnitPolicy(PolicyVerdict::DENY))]
final class DeniedSearchProduct extends UnitSearchProduct
{
}

#[Table('raxos_unit_search')]
#[Filter('q', new Text(modelKey: 'title'))]
#[Policy(new UnitPolicy(PolicyVerdict::DENY_SILENT))]
final class SilentSearchProduct extends UnitSearchProduct
{
}

#[Table('raxos_unit_tags')]
final class UnitSearchTag extends Model
{
    #[PrimaryKey] public int $id;
    #[Column] public int $product_id;
    #[Column] public string $state;
}

function searchUnitContext(): array
{
    $connection = new SQLite('sqlite::memory:');
    Db::register($connection);
    $connection->connect();
    $connection->pdo->exec('CREATE TABLE raxos_unit_search (id INTEGER PRIMARY KEY, title TEXT, group_id INTEGER, quantity INTEGER, enabled INTEGER, tag TEXT NULL, created_at TEXT)');
    $connection->pdo->exec("INSERT INTO raxos_unit_search VALUES (1,'apple',1,10,1,NULL,'2026-01-01 12:00:00'),(2,'banana',1,20,0,'blue','2026-01-02 12:00:00'),(3,'cherry',2,30,1,NULL,'2026-01-03 12:00:00')");
    $connection->pdo->exec('CREATE TABLE raxos_unit_tags (id INTEGER PRIMARY KEY, product_id INTEGER, state TEXT)');
    $connection->pdo->exec("INSERT INTO raxos_unit_tags VALUES (1,1,'blue'),(2,2,'red')");
    $structure = StructureGenerator::for(UnitSearchProduct::class);
    $query = new DatabaseQuery($connection)->select(['id'])->from(UnitSearchProduct::table())->orderBy('id');
    return [$connection, $structure, $query];
}
