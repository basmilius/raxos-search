<?php
declare(strict_types=1);

namespace Raxos\Search;

use Raxos\Contract\Database\Orm\OrmExceptionInterface;
use Raxos\Contract\Search\{AttributeInterface, SearchExceptionInterface};
use Raxos\Database\Orm\Model;
use Raxos\Database\Orm\Structure\StructureGenerator;
use Raxos\Search\Attribute\{Filter, Policy, Preset};
use Raxos\Search\Error\ReflectionErrorException;
use ReflectionException;
use function Raxos\Foundation\reflect;

/**
 * Class SearchModelGenerator
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Search
 * @since 2.0.0
 */
final class SearchModelGenerator
{

    /**
     * Generates a search model from a database model.
     *
     * @param class-string<Model> $modelClass
     *
     * @return SearchModel
     * @throws OrmExceptionInterface
     * @throws SearchExceptionInterface
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public static function generate(string $modelClass): SearchModel
    {
        try {
            $reflector = reflect($modelClass);
            $structure = StructureGenerator::for($modelClass);
            $attributes = $reflector->getAttributes(AttributeInterface::class);

            $filters = [];
            $policies = [];
            $presets = [];

            foreach ($attributes as $attribute) {
                switch (true) {
                    case $attribute instanceof Filter:
                        $filters[$attribute->property] = $attribute;
                        break;

                    case $attribute instanceof Policy:
                        $policies[] = $attribute->policy;
                        break;

                    case $attribute instanceof Preset:
                        $presets[] = $attribute;
                        break;
                }
            }

            return new SearchModel(
                $structure,
                $filters,
                $policies,
                $presets
            );
        } catch (ReflectionException $err) {
            throw new ReflectionErrorException($err);
        }
    }

}
