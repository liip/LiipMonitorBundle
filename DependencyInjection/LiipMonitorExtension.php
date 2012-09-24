<?php

namespace Liip\MonitorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\YamlFileLoader,
    Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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

        foreach ($config['checks'] as $check => $values) {
            $serviceId = $this->getAlias().'.check.'.$check;
            if ($container->hasDefinition($serviceId)) {
                $service = $container->getDefinition($serviceId);
            }

            switch ($check) {
                case 'custom_error_pages':
                    if (isset($values['error_codes'])) {
                        $service->replaceArgument(0, $values['error_codes']);
                    }
                    if (isset($values['kernel_root_dir'])) {
                        $service->replaceArgument(1, $values['kernel_root_dir']);
                    }
                    if (isset($values['twig.exception_listener.controller'])) {
                        $service->replaceArgument(2, $values['twig.exception_listener.controller']);
                    }
                    break;

                case 'symfony_version_check':
                    break;

                case 'deps_entries':
                    if (isset($values)) {
                        $service->replaceArgument(0, $values);
                    }
                    break;

                case 'memcache':
                    if (isset($values['host'])) {
                        $service->replaceArgument(0, $values['host']);
                    }
                    if (isset($values['port'])) {
                        $service->replaceArgument(1, $values['port']);
                    }
                    break;

                case 'php_extensions':
                    if (isset($values)) {
                        $service->replaceArgument(0, $values);
                    }
                    break;

                case 'process_active':
                    if (isset($values)) {
                        $service->replaceArgument(0, $values);
                    }
                    break;

                case 'writable_directory':
                    if (isset($values)) {
                        $service->replaceArgument(0, $values);
                    }
                    break;

                case 'disc_usage':
                    if (isset($values['percentage'])) {
                        $service->replaceArgument(0, $values['percentage']);
                    }
                    if (isset($values['path'])) {
                        $service->replaceArgument(1, $values['path']);
                    }
                    break;

                default:
                    if (!$container->hasDefinition($check)) {
                        throw new InvalidConfigurationException("LiipMonitorBundle does not provide a service definition for '$check'");
                    }
                    $service = $container->getDefinition($check);
            }

            $service->addTag('liip_monitor.check');
        }
    }
}
