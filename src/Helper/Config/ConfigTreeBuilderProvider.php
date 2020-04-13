<?php

namespace Mostertb\TransmissionTools\Helper\Config;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigTreeBuilderProvider implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('config', 'array');
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('clients')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name', false)
                    ->arrayPrototype()
                        ->canBeDisabled()
                        ->children()
                            ->scalarNode('name')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('host')
                                ->defaultValue('127.0.0.1')
                                ->cannotBeEmpty()
                            ->end()
                            ->integerNode('port')
                                ->defaultValue(9091)
                                ->min(1)
                                ->max(65535)
                            ->end()
                            ->scalarNode('username')
                                ->defaultNull()
                            ->end()
                            ->scalarNode('password')
                                ->defaultNull()
                             ->end()
                        ->end()
                    ->end()
                ->end()
                ->integerNode('http_timeout')
                    ->defaultValue(30)
                    ->info('Number of seconds after which to fail HTTP requests to Transmission Clients')
                    ->min(0)
                ->end()
            ->end()
         ->end();

        return $treeBuilder;
    }
}