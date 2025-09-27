<?php
declare(strict_types=1);

namespace Raxos\Search;

use Raxos\Collection\{ArrayList, Map};
use Raxos\Contract\Collection\{ArrayListInterface, MapInterface};
use Raxos\Contract\Database\DatabaseExceptionInterface;
use Raxos\Contract\Database\Query\QueryInterface;
use Raxos\Contract\Search\{SearchExceptionInterface, SearchProviderInterface};
use Raxos\Database\Orm\Model;
use Raxos\Database\Query\Select;
use Raxos\Search\Enum\PolicyVerdict;
use Raxos\Search\Error\IllegalSearchException;
use Raxos\Search\Query\{Lexer, Parser, Token as T};
use function array_map;
use function array_merge;
use function array_values;
use function Raxos\Database\Query\literal;

/**
 * Class SearchProvider
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search
 * @since 2.0.0
 */
final class SearchProvider implements SearchProviderInterface
{

    /** @var SearchModel[] */
    private array $models = [];

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function registerModel(string $modelClass): void
    {
        $this->models[$modelClass] ??= SearchModelGenerator::generate($modelClass);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function search(string $query, ?MapInterface $context = null, ?MapInterface $filters = null, int $limit = 10): ArrayListInterface
    {
        $context ??= new Map();
        $filters ??= new Map();
        $searchQuery = $this->tokenize($query);

        foreach ($searchQuery->nodes as $node) {
            match (true) {
                $node instanceof T\Field => $filters->set($node->key, $node->value),
                $node instanceof T\Phrase => $filters->set('q', $node)
            };
        }

        $results = new ArrayList();
        $rawResults = array_merge(...array_map(fn(SearchModel $model) => $this->searchModel($model, $context, $filters, $limit), array_values($this->models)));

        foreach ($rawResults as $result) {
            $score = (float)$result->backbone->data->getValue('__score');
            $results->append(new SearchResult($score, $result));
        }

        return $results
            ->sort(static fn(SearchResult $a, SearchResult $b) => $b->score <=> $a->score)
            ->slice(0, $limit);
    }

    /**
     * Search for a single model type.
     *
     * @param SearchModel $model
     * @param MapInterface $context
     * @param MapInterface $filters
     * @param int $limit
     *
     * @return Model[]
     * @throws DatabaseExceptionInterface
     * @throws SearchExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function searchModel(SearchModel $model, MapInterface $context, MapInterface $filters, int $limit): array
    {
        /** @var QueryInterface<Model> $query */
        $query = $model->structure->class::select();
        $query = new DatabaseQuery($query);

        foreach ($model->policies as $policy) {
            $decision = $policy->apply($model->structure, $query, $context);

            if ($decision->verdict === PolicyVerdict::ALLOW) {
                continue;
            }

            if ($decision->verdict === PolicyVerdict::DENY_SILENT) {
                return [];
            }

            throw new IllegalSearchException($decision);
        }

        $didFilter = false;
        $scoreExpressions = [];

        foreach ($model->filters as $key => $filterAttr) {
            if (!$filters->has($key)) {
                continue;
            }

            $didFilter = true;
            $filter = $filterAttr->filter;
            $scoreExpressions[] = $filter->apply($model->structure, $filterAttr, $query, $filters->get($key));
        }

        if (!$didFilter) {
            return [];
        }

        $query->select(new Select()->add(
            __score: new ScoreExpressions($scoreExpressions)
        ));

        return $query
            ->withModel($model->structure->class)
            ->orderByDesc(literal('__score'))
            ->limit($limit)
            ->array();
    }

    /**
     * Tokenize the search query.
     *
     * @param string $query
     *
     * @return T\Query
     * @throws SearchExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    private function tokenize(string $query): T\Query
    {
        $lexer = new Lexer($query);
        $tokens = $lexer->tokenize();

        $parser = new Parser($tokens);

        return $parser->parse();
    }

}
