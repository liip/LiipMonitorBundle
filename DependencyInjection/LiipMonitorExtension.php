<?php

namespace Liip\MonitorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\YamlFileLoader,
    Symfony\Component\Config\Definition\Exception\InvalidConfigurationException,
    Symfony\Component\DependencyInjection\Reference;

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

        foreach ($config['checks'] as $check => $values) {
            if (empty($values)) {
                continue;
            }

            $serviceId = $this->getAlias().'.check.'.$check;
            $service = $container->getDefinition($serviceId);
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

                case 'extension_loaded':
                    $service->addArgument($values);
                    break;

                case 'process_running':
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

            $service->addTag('liip_monitor.check', array('alias' => $check));
        }
    }
}
