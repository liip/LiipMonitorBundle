<?php

namespace Liip\MonitorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle.
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
            ->beforeNormalization()
                ->always(function ($v) {
                    if (empty($v['default_group'])) {
                        $v['default_group'] = 'default';
                    }

                    if (isset($v['checks']) && is_array($v['checks']) && !isset($v['checks']['groups'])) {
                        $checks = $v['checks'];
                        unset($v['checks']);

                        $v['checks']['groups'][$v['default_group']] = $checks;
                    }

                    return $v;
                })
            ->end()
            ->children()
                ->booleanNode('enable_controller')->defaultFalse()->end()
                ->scalarNode('view_template')->defaultNull()->end()
                ->arrayNode('mailer')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('recipient')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('sender')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('subject')->isRequired()->cannotBeEmpty()->end()
                        ->booleanNode('send_on_warning')->defaultTrue()->end()
                    ->end()
                ->end()
                ->scalarNode('default_group')->defaultValue('default')->end()
                ->arrayNode('checks')
                    ->canBeUnset()
                    ->children()
                        ->append($this->createGroupsNode())
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    private function createGroupsNode()
    {
        $builder = new TreeBuilder();

        $node = $builder->root('groups', 'array');
        $node
            ->requiresAtLeastOneElement()
            ->info('Grouping checks')
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->arrayNode('php_extensions')
                        ->info('Validate that a named extension or a collection of extensions is available')
                        ->example('session.use_only_cookies: false')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('php_flags')
                        ->info('Pairs of a PHP setting and an expected value')
                        ->example('session.use_only_cookies: false')
                        ->useAttributeAsKey('setting')
                        ->prototype('scalar')->defaultValue(true)->end()
                    ->end()
                    ->arrayNode('php_version')
                        ->info('Pairs of a version and a comparison operator')
                        ->example('5.4.15: >=')
                        ->useAttributeAsKey('version')
                        ->prototype('scalar')->end()
                    ->end()
                    ->variableNode('process_running')
                        ->info('Process name/pid or an array of process names/pids')
                        ->example('[apache, foo]')
                    ->end()
                    ->arrayNode('readable_directory')
                        ->info('Validate that a given path (or a collection of paths) is a dir and is readable')
                        ->example('["%kernel.cache_dir%"]')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('writable_directory')
                        ->info('Validate that a given path (or a collection of paths) is a dir and is writable')
                        ->example('["%kernel.cache_dir%"]')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('class_exists')
                        ->info('Validate that a class or a collection of classes is available')
                        ->example('["Lua", "My\Fancy\Class"]')
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('cpu_performance')
                        ->info('Benchmark CPU performance and return failure if it is below the given ratio')
                        ->example('1.0 # This is the power of an EC2 micro instance')
                    ->end()
                    ->arrayNode('disk_usage')
                        ->info('Checks to see if the disk usage is below warning/critical percent thresholds')
                        ->children()
                            ->integerNode('warning')->defaultValue(70)->end()
                            ->integerNode('critical')->defaultValue(90)->end()
                            ->scalarNode('path')->defaultValue('%kernel.cache_dir%')->end()
                        ->end()
                    ->end()
                    ->arrayNode('symfony_requirements')
                        ->info('Checks Symfony2 requirements file')
                        ->children()
                            ->scalarNode('file')->defaultValue('%kernel.root_dir%/SymfonyRequirements.php')->end()
                        ->end()
                    ->end()
                    ->arrayNode('opcache_memory')
                        ->info('Checks to see if the OpCache memory usage is below warning/critical thresholds')
                        ->children()
                            ->integerNode('warning')->defaultValue(70)->end()
                            ->integerNode('critical')->defaultValue(90)->end()
                        ->end()
                    ->end()
                    ->arrayNode('apc_memory')
                        ->info('Checks to see if the APC memory usage is below warning/critical thresholds')
                        ->children()
                            ->integerNode('warning')->defaultValue(70)->end()
                            ->integerNode('critical')->defaultValue(90)->end()
                        ->end()
                    ->end()
                    ->arrayNode('apc_fragmentation')
                        ->info('Checks to see if the APC fragmentation is below warning/critical thresholds')
                        ->children()
                            ->integerNode('warning')->defaultValue(70)->end()
                            ->integerNode('critical')->defaultValue(90)->end()
                        ->end()
                    ->end()
                    ->variableNode('doctrine_dbal')
                        ->defaultNull()
                        ->info('Connection name or an array of connection names')
                        ->example('[default, crm]')
                    ->end()
                    ->arrayNode('memcache')
                        ->info('Check if MemCache extension is loaded and given server is reachable')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('host')->defaultValue('localhost')->end()
                                ->integerNode('port')->defaultValue(11211)->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('redis')
                        ->info('Validate that a Redis service is running')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('host')->defaultValue('localhost')->end()
                                ->integerNode('port')->defaultValue(6379)->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('http_service')
                        ->info('Attempt connection to given HTTP host and (optionally) check status code and page content')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('host')->defaultValue('localhost')->end()
                                ->integerNode('port')->defaultValue(80)->end()
                                ->scalarNode('path')->defaultValue('/')->end()
                                ->integerNode('status_code')->defaultValue(200)->end()
                                ->scalarNode('content')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('guzzle_http_service')
                        ->info('Attempt connection using Guzzle to given HTTP host and (optionally) check status code and page content')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('url')->defaultValue('localhost')->end()
                                ->variableNode('headers')->defaultValue(array())->end()
                                ->variableNode('options')->defaultValue(array())->end()
                                ->integerNode('status_code')->defaultValue(200)->end()
                                ->scalarNode('content')->defaultNull()->end()
                                ->scalarNode('method')->defaultValue('GET')->end()
                                ->scalarNode('body')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('rabbit_mq')
                        ->info('Validate that a RabbitMQ service is running')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('host')->defaultValue('localhost')->end()
                                ->integerNode('port')->defaultValue(5672)->end()
                                ->scalarNode('user')->defaultValue('guest')->end()
                                ->scalarNode('password')->defaultValue('guest')->end()
                                ->scalarNode('vhost')->defaultValue('/')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->booleanNode('symfony_version')
                        ->info('Checks the version of this app against the latest stable release')
                    ->end()
                    ->arrayNode('custom_error_pages')
                        ->info('Checks if error pages have been customized for given error codes')
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
                        ->info('Checks installed composer dependencies against the SensioLabs Security Advisory database')
                        ->children()
                            ->scalarNode('lock_file')->defaultValue('%kernel.root_dir%'.'/../composer.lock')->end()
                        ->end()
                    ->end()
                    ->arrayNode('stream_wrapper_exists')
                        ->info('Validate that a stream wrapper or collection of stream wrappers exists')
                        ->example('[\'zlib\', \'bzip2\', \'zip\']')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('file_ini')
                        ->info('Find and validate INI files')
                        ->example('[\'path/to/my.ini\']')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('file_json')
                        ->info('Find and validate JSON files')
                        ->example('[\'path/to/my.json\']')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('file_xml')
                        ->info('Find and validate XML files')
                        ->example('[\'path/to/my.xml\']')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('file_yaml')
                        ->info('Find and validate YAML files')
                        ->example('[\'path/to/my.yml\']')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('expressions')
                        ->useAttributeAsKey('alias')
                        ->info('Checks that fail/warn when given expression is false (expressions are evaluated with symfony/expression-language)')
                        ->example(array(
                            'opcache' => array(
                                'label' => 'OPcache',
                                'warning_expression' => "ini('opcache.revalidate_freq') > 0",
                                'critical_expression' => "ini('opcache.enable')",
                                'warning_message' => 'OPcache not optimized for production',
                                'critical_message' => 'OPcache not enabled',
                            ),
                        ))
                        ->prototype('array')
                            ->addDefaultsIfNotSet()
                            ->validate()
                                ->ifTrue(function ($value) { return !$value['warning_expression'] && !$value['critical_expression']; })
                                ->thenInvalid('A warning_expression or a critical_expression must be set.')
                            ->end()
                            ->children()
                                ->scalarNode('label')->isRequired()->end()
                                ->scalarNode('warning_expression')
                                ->defaultNull()
                                ->example('ini(\'apc.stat\') == 0')
                            ->end()
                            ->scalarNode('critical_expression')
                            ->defaultNull()
                            ->example('ini(\'short_open_tag\') == 1')
                        ->end()
                        ->scalarNode('warning_message')->defaultNull()->end()
                        ->scalarNode('critical_message')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
