<?php

namespace Liip\MonitorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;

class LiipMonitorExtension extends Extension
{
    /**
     * Loads the services based on your application configuration.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader =  new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('runner.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (null === $config['view_template']) {
            $config['view_template'] = __DIR__ . '/../Resources/views/health/index.html.php';
        }

        if ($config['enable_controller']) {
            $container->setParameter(sprintf('%s.view_template', $this->getAlias()), $config['view_template']);
            $loader->load('controller.xml');
        }

        if ($config['mailer']['enabled']) {
            $loader->load('helper/swift_mailer.xml');

            foreach ($config['mailer'] as $key => $value) {
                $container->setParameter(sprintf('%s.mailer.%s', $this->getAlias(), $key), $value);
            }
        }

        if (empty($config['checks'])) {
            return;
        }

        foreach ($config['checks'] as $check => $values) {
            if (empty($values)) {
                continue;
            }

            $loader->load('checks/'.$check.'.xml');
            $prefix = sprintf('%s.check.%s', $this->getAlias(), $check);

            switch ($check) {
                case 'class_exists':
                case 'cpu_performance':
                case 'php_extensions':
                case 'php_version':
                case 'php_flags':
                case 'readable_directory':
                case 'writable_directory':
                case 'process_running':
                case 'doctrine_dbal':
                case 'http_service':
                case 'guzzle_http_service':
                case 'memcache':
                case 'redis':
                case 'rabbit_mq':
                case 'stream_wrapper_exists':
                case 'file_ini':
                case 'file_json':
                case 'file_xml':
                case 'file_yaml':
                case 'expressions':
                    $container->setParameter($prefix, $values);
                    continue;

                case 'symfony_version':
                    continue;
            }

            if (is_array($values)) {
                foreach ($values as $key => $value) {
                    $container->setParameter($prefix . '.' . $key, $value);
                }
            }
        }
    }
}
