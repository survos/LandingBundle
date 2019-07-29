<?php

namespace Survos\LandingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $rootName = 'survos_landing';
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder($rootName);
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->Root($rootName);
        }

        $rootNode
            ->children()
            ->arrayNode('entities')
                ->normalizeKeys(false)
                // ->useAttributeAsKey('name', false)
                ->defaultValue(array())
                ->info('The list of entities to manage in the administration zone.')
                ->prototype('variable')
                ->end()
//            ->booleanNode('unicorns_are_real')->defaultTrue()->end()
//            ->integerNode('min_sunshine')->defaultValue(3)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
