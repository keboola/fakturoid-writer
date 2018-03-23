<?php

namespace Keboola\FakturoidWriter;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigDefinition implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('parameters');

        $rootNode
            ->children()
                ->scalarNode('email')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('#token')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('slug')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->enumNode('order')
                    ->values(['asc', 'desc'])
                    ->defaultValue('asc')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
