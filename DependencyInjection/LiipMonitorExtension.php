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
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('runner.xml');
        $loader->load('helper.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (null === $config['view_template']) {
            $config['view_template'] = __DIR__.'/../Resources/views/health/index.html.php';
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

        $container->setParameter(sprintf('%s.default_group', $this->getAlias()), $config['default_group']);

        if (empty($config['checks'])) {
            return;
        }

        $checksLoaded = array();
        $containerParams = array();
        foreach ($config['checks']['groups'] as $group => $checks) {
            if (empty($checks)) {
                continue;
            }

            foreach ($checks as $check => $values) {
                if (empty($values)) {
                    continue;
                }

                $containerParams['groups'][$group][$check] = $values;
                $this->setParameters($container, $check, $group, $values);

                if (!in_array($check, $checksLoaded)) {
                    $loader->load('checks/'.$check.'.xml');
                    $checksLoaded[] = $check;
                }
            }
        }

        $container->setParameter(sprintf('%s.checks', $this->getAlias()), $containerParams);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $checkName
     * @param string           $group
     * @param array            $values
     */
    private function setParameters(ContainerBuilder $container, $checkName, $group, $values)
    {
        $prefix = sprintf('%s.check.%s', $this->getAlias(), $checkName);
        switch ($checkName) {
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
                $container->setParameter($prefix.'.'.$group, $values);
                continue;

            case 'symfony_version':
                continue;

            case 'opcache_memory':
                if (!class_exists('ZendDiagnostics\Check\OpCacheMemory')) {
                    throw new \InvalidArgumentException('Please require at least "v1.0.4" of "ZendDiagnostics"');
                }
                continue;

            case 'doctrine_migrations':
                if (!class_exists('ZendDiagnostics\Check\DoctrineMigration')) {
                    throw new \InvalidArgumentException('Please require at least "v1.0.6" of "ZendDiagnostics"');
                }

                if (!class_exists('Doctrine\Bundle\MigrationsBundle\Command\DoctrineCommand')) {
                    throw new \InvalidArgumentException('Please require at least "v1.0.0" of "DoctrineMigrationsBundle"');
                }

                if (!class_exists('Doctrine\DBAL\Migrations\Configuration\Configuration')) {
                    throw new \InvalidArgumentException('Please require at least "v1.1.0" of "Doctrine Migrations Library"');
                }

                $container->setParameter($prefix.'.'.$group, $values);
                continue;

            case 'pdo_connections':
                if (!class_exists('ZendDiagnostics\Check\PDOCheck')) {
                    throw new \InvalidArgumentException('Please require at least "v1.0.5" of "ZendDiagnostics"');
                }
                $container->setParameter($prefix.'.'.$group, $values);
                continue;

        }

        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $container->setParameter($prefix.'.'.$key.'.'.$group, $value);
            }
        }
    }
}
