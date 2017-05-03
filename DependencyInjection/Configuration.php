<?php

namespace Splash\Sylius\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('app');

        $rootNode
            ->children()
                //====================================================================//
                // COMMON Parameters
                //====================================================================//
                ->scalarNode('default_channel')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('Default Channel for association with new Products.')
                ->end()   
                ->scalarNode('images_folder')
                    ->cannotBeEmpty()
                     ->defaultValue("%kernel.root_dir%/../web/media/image")               
                    ->info('Default Channel for association with new Products.')
                ->end()                  
            ->end()
        ;
        return $treeBuilder;
    }
}