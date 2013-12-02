<?php

namespace Liip\MonitorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\YamlFileLoader,
    Symfony\Component\Config\Definition\Exception\InvalidConfigurationException,
    Symfony\Component\DependencyInjection\Reference,
    Symfony\Component\DependencyInjection\Definition;

class LiipMonitorExtension extends Extension
{
    /**
     * Loads the services based on your application configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader =  new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('config.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (empty($config['checks'])) {
            return;
        }

        //This list of checks can have multiple configurations defined
        $dynamic_checks = array('http_services');

        foreach ($config['checks'] as $check => $values) {
            if (empty($values)) {
                continue;
            }

            $serviceId = $this->getAlias().'.check.'.$check;
            $service = null;
            if (!in_array($check, $dynamic_checks)) {
                $service = $container->getDefinition($serviceId);
            }
            switch ($check) {
                case 'custom_error_pages':
                    $service->addArgument($values['error_codes']);
                    $service->addArgument($values['path']);
                    $service->addArgument($values['controller']);
                    break;

                case 'symfony_version_check':
                    break;

                case 'deps_entries':
                    if (!is_string($values)) {
                        $values = '%kernel.root_dir%';
                    }
                    $service->addArgument($values);
                    break;

                case 'memcache':
                    $service->replaceArgument(0, $values['host']);
                    $service->replaceArgument(1, $values['port']);
                    break;

                case 'doctrine_dbal':
                    $service->addArgument(new Reference('doctrine'));
                    $service->addArgument($values);
                    break;

                case 'http_service':
                    $service->replaceArgument(0, $values['host']);
                    $service->replaceArgument(1, $values['port']);
                    $service->replaceArgument(2, $values['path']);
                    $service->replaceArgument(3, $values['status_code']);
                    $service->replaceArgument(4, $values['content']);
                    break;

                case 'http_services':
                    foreach ($values as $alias => $service_values) {
                        $new_id = $serviceId . '_' . $alias;
                        if ($container->hasDefinition($new_id)) {
                            $service = $container->getDefinition($new_id);
                        } else {
                            $http_service = $container->getDefinition($this->getAlias().'.check.http_service');
                            $service = $container->setDefinition($new_id, new Definition($http_service->getClass(), $http_service->getArguments()));
                        }
                        $service->replaceArgument(0, $service_values['host']);
                        $service->replaceArgument(1, $service_values['port']);
                        $service->replaceArgument(2, $service_values['path']);
                        $service->replaceArgument(3, $service_values['status_code']);
                        $service->replaceArgument(4, $service_values['content']);
                        $service->addTag('liip_monitor.check', array('alias' => $check . '_' . $alias));
                    }
                    break;
                case 'php_extensions':
                    $service->addArgument($values);
                    break;

                case 'process_active':
                    $service->addArgument($values);
                    break;

                case 'writable_directory':
                    $service->addArgument($values);
                    break;

                case 'disc_usage':
                    $service->addArgument($values['percentage']);
                    $service->addArgument($values['path']);
                    break;

                case 'security_advisory':
                    $service->addArgument($values['lock_file']);
                    break;
            }

            if (!in_array($check, $dynamic_checks)) {
                $service->addTag('liip_monitor.check', array('alias' => $check));
            }
        }
    }
}
