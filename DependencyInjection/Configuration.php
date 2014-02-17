<?php

namespace Liip\MonitorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('liip_monitor', 'array');

        $rootNode
            ->children()
                ->booleanNode('enable_controller')->defaultFalse()->end()
                ->arrayNode('checks')
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('php_extensions')
                            ->prototype('scalar')->end()
                        ->end()
                        ->variableNode('process_running')
                            ->info('Process name/pid or an array of process names/pids.')
                            ->example('[apache, foo]')
                        ->end()
                        ->arrayNode('writable_directory')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('disk_usage')
                            ->children()
                                ->scalarNode('warning')->defaultValue('70')->end()
                                ->scalarNode('critical')->defaultValue('90')->end()
                                ->scalarNode('path')->defaultValue('%kernel.cache_dir%')->end()
                            ->end()
                        ->end()
                        ->arrayNode('symfony_requirements')
                            ->children()
                                ->scalarNode('file')->defaultValue('%kernel.root_dir%/SymfonyRequirements.php')->end()
                            ->end()
                        ->end()
                        ->arrayNode('apc_memory')
                            ->children()
                                ->scalarNode('warning')->defaultValue('70')->end()
                                ->scalarNode('critical')->defaultValue('90')->end()
                            ->end()
                        ->end()
                        ->arrayNode('apc_fragmentation')
                            ->children()
                                ->scalarNode('warning')->defaultValue('70')->end()
                                ->scalarNode('critical')->defaultValue('90')->end()
                            ->end()
                        ->end()
                        ->scalarNode('doctrine_dbal')->defaultNull()->end()
                        ->arrayNode('memcache')
                            ->children()
                                ->scalarNode('host')->defaultValue('localhost')->end()
                                ->scalarNode('port')->defaultValue('11211')->end()
                            ->end()
                        ->end()
                        ->arrayNode('redis')
                            ->children()
                                ->scalarNode('host')->defaultValue('localhost')->end()
                                ->scalarNode('port')->defaultValue('6379')->end()
                            ->end()
                        ->end()
                        ->arrayNode('http_service')
                            ->children()
                            ->scalarNode('host')->defaultValue('localhost')->end()
                            ->scalarNode('port')->defaultValue('80')->end()
                            ->scalarNode('path')->defaultValue('/')->end()
                            ->scalarNode('status_code')->defaultValue('200')->end()
                            ->scalarNode('content')->defaultNull()->end()
                            ->end()
                        ->end()
                        ->arrayNode('rabbit_mq')
                            ->children()
                            ->scalarNode('host')->defaultValue('localhost')->end()
                            ->scalarNode('port')->defaultValue('5672')->end()
                            ->scalarNode('user')->defaultValue('guest')->end()
                            ->scalarNode('password')->defaultValue('guest')->end()
                            ->scalarNode('vhost')->defaultValue('/')->end()
                            ->end()
                        ->end()
                        ->scalarNode('dep_entries')->end()
                        ->booleanNode('symfony_version')->end()
                        ->arrayNode('custom_error_pages')
                            ->children()
                                ->arrayNode('error_codes')
                                    ->isRequired()
                                    ->requiresAtLeastOneElement()
                                    ->prototype('scalar')->end()
                                ->end()
                                ->scalarNode('path')->defaultValue('%kernel.root_dir%')->end()
                                ->scalarNode('controller')->defaultValue('%twig.exception_listener.controller%')->end()
                            ->end()
                        ->end()
                        ->arrayNode('security_advisory')
                            ->children()
                                ->scalarNode('lock_file')->defaultValue('%kernel.root_dir%' . '/../composer.lock')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

}
