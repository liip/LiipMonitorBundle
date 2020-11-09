<?php

namespace Liip\MonitorBundle\DependencyInjection;

use InvalidArgumentException;
use Symfony\Component\Config\Definition\BaseNode;
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
        $treeBuilder = new TreeBuilder('liip_monitor');

        // Keep compatibility with symfony/config < 4.2
        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('liip_monitor');
        }

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
                ->integerNode('failure_status_code')
                    ->min(100)->max(598)
                    ->defaultValue(502)
                ->end()
                ->arrayNode('mailer')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('recipient')
                            ->isRequired()->requiresAtLeastOneElement()
                            ->prototype('scalar')->end()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) { return [$v]; })
                            ->end()
                        ->end()
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
        $builder = new TreeBuilder('groups');

        // Keep compatibility with symfony/config < 4.2
        if (\method_exists($builder, 'getRootNode')) {
            $node = $builder->getRootNode();
        } else {
            $node = $builder->root('groups');
        }
        $node
            ->requiresAtLeastOneElement()
            ->info('Grouping checks')
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->arrayNode('php_extensions')
                        ->info('Validate that a named extension or a collection of extensions is available')
                        ->example('session.use_only_cookies: false')
                        ->prototype('variable')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($value) { return ['name' => $value]; })
                            ->end()
                            ->validate()
                                ->ifArray()
                                ->then(function ($value) {
                                    if (!isset($value['name'])) {
                                        throw new InvalidArgumentException('You should define an extension name');
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('php_flags')
                        ->info('Pairs of a PHP setting and an expected value')
                        ->example('session.use_only_cookies: false')
                        ->useAttributeAsKey('setting')
                        ->beforeNormalization()
                            ->always()
                            ->then(function ($flags) {
                                foreach ($flags as $flagName => $flagValue) {
                                    if (is_scalar($flagValue)) {
                                        $flags[$flagName] = [
                                            'value' => $flagValue,
                                        ];
                                    }
                                }

                                return $flags;
                            })
                        ->end()
                        ->prototype('variable')
                            ->validate()
                                ->ifArray()
                                ->then(function ($value) {
                                    if (!isset($value['value'])) {
                                        throw new InvalidArgumentException('You should define a value of php flag');
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('php_version')
                        ->info('Pairs of a version and a comparison operator')
                        ->example('5.4.15: >=')
                        ->useAttributeAsKey('version')
                        ->beforeNormalization()
                            ->always()
                            ->then(function ($versions) {
                                foreach ($versions as $version => $value) {
                                    if (is_scalar($value)) {
                                        $versions[$version] = [
                                            'operator' => $value,
                                        ];
                                    }
                                }

                                return $versions;
                            })
                        ->end()
                        ->prototype('variable')
                            ->validate()
                                ->ifArray()
                                ->then(function ($value) {
                                    if (!isset($value['operator'])) {
                                        throw new InvalidArgumentException('You should define a comparison operator');
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                    ->end()
                    ->variableNode('process_running')
                        ->info('Process name/pid or an array of process names/pids')
                        ->example('[apache, foo]')
                        ->beforeNormalization()
                            ->always()
                            ->then(function ($value) {
                                if (is_array($value)) {
                                    foreach ($value as $key => $process) {
                                        if (is_scalar($process)) {
                                            $value[$key] = [
                                                'name' => $process,
                                            ];
                                        }
                                    }
                                } else {
                                    $value = [
                                        ['name' => $value],
                                    ];
                                }

                                return $value;
                            })
                        ->end()
                        ->validate()
                            ->always()
                            ->then(function ($value) {
                                foreach ($value as $process) {
                                    if (!isset($process['name'])) {
                                        throw new InvalidArgumentException('You should define a process name');
                                    }
                                }

                                return $value;
                            })
                        ->end()
                    ->end()
                    ->arrayNode('readable_directory')
                        ->info('Validate that a given path (or a collection of paths) is a dir and is readable')
                        ->example('["%kernel.cache_dir%"]')
                        ->prototype('variable')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($value) { return ['path' => $value]; })
                            ->end()
                            ->validate()
                                ->ifArray()
                                ->then(function ($value) {
                                    if (!isset($value['path'])) {
                                        throw new InvalidArgumentException('You should define a directory path');
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('writable_directory')
                        ->info('Validate that a given path (or a collection of paths) is a dir and is writable')
                        ->example('["%kernel.cache_dir%"]')
                        ->prototype('variable')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($value) { return ['path' => $value]; })
                            ->end()
                            ->validate()
                                ->ifArray()
                                ->then(function ($value) {
                                    if (!isset($value['path'])) {
                                        throw new InvalidArgumentException('You should define a directory path');
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('class_exists')
                        ->info('Validate that a class or a collection of classes is available')
                        ->example('["Lua", "My\Fancy\Class"]')
                        ->prototype('variable')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($value) { return ['name' => $value]; })
                            ->end()
                            ->validate()
                                ->ifArray()
                                ->then(function ($value) {
                                    if (!isset($value['name'])) {
                                        throw new InvalidArgumentException('You should define a class name');
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
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
                            ->scalarNode('label')->defaultNull()->end()
                        ->end()
                    ->end()
                    ->arrayNode('symfony_requirements')
                        ->info('Checks Symfony2 requirements file')
                        ->children()
                            ->scalarNode('file')->defaultValue('%kernel.root_dir%/SymfonyRequirements.php')->end()
                            ->scalarNode('label')->defaultNull()->end()
                        ->end()
                    ->end()
                    ->arrayNode('opcache_memory')
                        ->info('Checks to see if the OpCache memory usage is below warning/critical thresholds')
                        ->children()
                            ->integerNode('warning')->defaultValue(70)->end()
                            ->integerNode('critical')->defaultValue(90)->end()
                            ->scalarNode('label')->defaultNull()->end()
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
                    ->variableNode('doctrine_mongodb')
                        ->defaultNull()
                        ->info('Connection name or an array of connection names')
                        ->example('[default, crm]')
                    ->end()
                    ->arrayNode('doctrine_migrations')
                        ->useAttributeAsKey('name')
                        ->info('Checks to see if migrations from specified configuration file are applied')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('configuration_file')
                                    ->info('Absolute path to doctrine migrations configuration')
                                ->end()
                                ->scalarNode('connection')
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->info('Connection name from doctrine DBAL configuration')
                                ->end()
                            ->end()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($value) {
                                    if (is_string($value)) {
                                        $value = ['connection' => $value];
                                    }

                                    return $value;
                                })
                            ->end()
                            ->validate()
                                ->always(function ($value) {
                                    if (is_array($value) && !isset($value['configuration_file']) && !class_exists('Doctrine\\Bundle\\MigrationsBundle\\Command\\DoctrineCommand')) {
                                        throw new InvalidArgumentException('You should explicitly define "configuration_file" parameter or install doctrine/doctrine-migrations-bundle to use empty parameter.');
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->example(
                            [
                                'application_migrations' => [
                                    'configuration_file' => '%kernel.root_dir%/Resources/config/migrations.yml',
                                    'connection' => 'default',
                                ],
                                'migrations_with_doctrine_bundle' => [
                                    'connection' => 'default',
                                ],
                                'migrations_with_doctrine_bundle_v2' => 'default',
                            ]
                        )
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
                    ->arrayNode('memcached')
                        ->info('Check if MemCached extension is loaded and given server is reachable')
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
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) { return ['dsn' => $v]; })
                        ->end()
                        ->prototype('array')
                            ->children()
                                ->scalarNode('host')->defaultValue('localhost')->end()
                                ->integerNode('port')->defaultValue(6379)->end()
                                ->scalarNode('password')->defaultNull()->end()
                                ->scalarNode('dsn')->defaultNull()->end()
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
                                ->variableNode('headers')->defaultValue([])->end()
                                ->variableNode('options')->defaultValue([])->end()
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
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) { return ['dsn' => $v]; })
                        ->end()
                        ->prototype('array')
                            ->children()
                                ->scalarNode('host')->defaultValue('localhost')->end()
                                ->integerNode('port')->defaultValue(5672)->end()
                                ->scalarNode('user')->defaultValue('guest')->end()
                                ->scalarNode('password')->defaultValue('guest')->end()
                                ->scalarNode('vhost')->defaultValue('/')->end()
                                ->scalarNode('dsn')->defaultNull()->end()
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
                                ->example('[404, 503]')
                                ->info('The status codes that should be customized')
                                ->isRequired()
                                ->requiresAtLeastOneElement()
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('path')
                                ->info('The directory where your custom error page twig templates are located. Keep as "%kernel.project_dir%" to use default location.')
                                ->defaultValue('%kernel.project_dir%')
                            ->end()
                            ->scalarNode('controller')
                                ->defaultNull()
                                ->setDeprecated(...self::getCustomErrorPagesControllerDeprecationMessage())
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('security_advisory')
                        ->info('Checks installed composer dependencies against the SensioLabs Security Advisory database')
                        ->children()
                            ->scalarNode('lock_file')->defaultValue('%kernel.project_dir%/composer.lock')->end()
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
                    ->arrayNode('pdo_connections')
                        ->info('PDO connections to check for connection')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('dsn')->defaultNull()->end()
                                ->scalarNode('username')->defaultNull()->end()
                                ->scalarNode('password')->defaultNull()->end()
                                ->integerNode('timeout')->defaultValue(1)->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('expressions')
                        ->useAttributeAsKey('alias')
                        ->info('Checks that fail/warn when given expression is false (expressions are evaluated with symfony/expression-language)')
                        ->example([
                            'opcache' => [
                                'label' => 'OPcache',
                                'warning_expression' => "ini('opcache.revalidate_freq') > 0",
                                'critical_expression' => "ini('opcache.enable')",
                                'warning_message' => 'OPcache not optimized for production',
                                'critical_message' => 'OPcache not enabled',
                            ],
                        ])
                        ->prototype('array')
                            ->addDefaultsIfNotSet()
                            ->validate()
                                ->ifTrue(function ($value) {
                                    return !$value['warning_expression'] && !$value['critical_expression'];
                                })
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

    /**
     * Returns the correct deprecation param's as an array for setDeprecated.
     *
     * Symfony/Config v5.1 introduces a deprecation notice when calling
     * setDeprecation() with less than 3 args and the getDeprecation method was
     * introduced at the same time. By checking if getDeprecation() exists,
     * we can determine the correct param count to use when calling setDeprecated.
     */
    private static function getCustomErrorPagesControllerDeprecationMessage()
    {
        $message = 'The custom error page controller option is no longer used; the corresponding config parameter was deprecated in 2.13 and will be dropped in 3.0.';

        if (method_exists(BaseNode::class, 'getDeprecation')) {
            return [
                'liip/monitor-bundle',
                '2.13',
                $message,
            ];
        }

        return [$message];
    }
}
