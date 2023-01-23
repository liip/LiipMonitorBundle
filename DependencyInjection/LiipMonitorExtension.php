<?php

namespace Liip\MonitorBundle\DependencyInjection;

use Doctrine\Migrations\Configuration\Configuration as DoctrineMigrationConfiguration;
use Liip\MonitorBundle\DependencyInjection\DoctrineMigrations\DoctrineMigrationsLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LiipMonitorExtension extends Extension implements CompilerPassInterface
{
    /**
     * Loader for doctrine migrations to support both v2 and v3 major versions.
     *
     * @var DoctrineMigrationsLoader
     */
    private $migrationsLoader;

    /**
     * LiipMonitorExtension constructor.
     */
    public function __construct()
    {
        $this->migrationsLoader = new DoctrineMigrationsLoader();
    }

    /**
     * Loads the services based on your application configuration.
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('runner.xml');
        $loader->load('helper.xml');
        $loader->load('commands.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (null === $config['view_template']) {
            $config['view_template'] = __DIR__.'/../Resources/views/health/index.html.php';
        }

        if ($config['enable_controller']) {
            $container->setParameter(sprintf('%s.view_template', $this->getAlias()), $config['view_template']);
            $container->setParameter(sprintf('%s.failure_status_code', $this->getAlias()), $config['failure_status_code']);
            $loader->load('controller.xml');
        }

        $this->configureMailer($container, $config);

        $container->setParameter(sprintf('%s.default_group', $this->getAlias()), $config['default_group']);

        // symfony3 does not define templating.helper.assets unless php templating is included
        if ($container->has('templating.helper.assets')) {
            $pathHelper = $container->getDefinition('liip_monitor.helper');
            $pathHelper->replaceArgument(0, 'templating.helper.assets');
        }

        // symfony3 does not define templating.helper.router unless php templating is included
        if ($container->has('templating.helper.router')) {
            $pathHelper = $container->getDefinition('liip_monitor.helper');
            $pathHelper->replaceArgument(1, 'templating.helper.router');
        }

        if (empty($config['checks'])) {
            return;
        }

        $checksLoaded = [];
        $containerParams = [];
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
        $this->configureDoctrineMigrationsCheck($container, $containerParams);
    }

    public function process(ContainerBuilder $container): void
    {
        $this->migrationsLoader->process($container);
    }

    /**
     * @param string $checkName
     * @param string $group
     * @param array  $values
     */
    private function setParameters(ContainerBuilder $container, $checkName, $group, $values): void
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
            case 'doctrine_mongodb':
            case 'http_service':
            case 'guzzle_http_service':
            case 'memcache':
            case 'memcached':
            case 'redis':
            case 'rabbit_mq':
            case 'stream_wrapper_exists':
            case 'file_ini':
            case 'file_json':
            case 'file_xml':
            case 'file_yaml':
            case 'expressions':
            case 'pdo_connections':
            case 'messenger_transports':
                $container->setParameter($prefix.'.'.$group, $values);
                break;
            case 'symfony_version':
            case 'opcache_memory':
                break;

            case 'doctrine_migrations':
                if (!class_exists(DoctrineMigrationConfiguration::class)) {
                    throw new \InvalidArgumentException('Please require at least "v2.0.0" of "Doctrine Migrations Library"');
                }

                $container->setParameter($prefix.'.'.$group, $values);
                break;
        }

        if (is_array($values)) {
            foreach ($values as $key => $value) {
                $container->setParameter($prefix.'.'.$key.'.'.$group, $value);
            }
        }
    }

    /**
     * Set up doctrine migration configuration services.
     *
     * @param ContainerBuilder $container The container
     * @param array            $params    Container params
     */
    private function configureDoctrineMigrationsCheck(ContainerBuilder $container, array $params): void
    {
        if (!$container->hasDefinition('liip_monitor.check.doctrine_migrations') || !isset($params['groups'])) {
            return;
        }

        foreach ($params['groups'] as $groupName => $groupChecks) {
            if (!isset($groupChecks['doctrine_migrations'])) {
                continue;
            }

            $services = $this->migrationsLoader->loadMigrationChecks(
                $container,
                $groupChecks['doctrine_migrations'],
                $groupName
            );

            $parameter = sprintf('%s.check.%s.%s', $this->getAlias(), 'doctrine_migrations', $groupName);
            $container->setParameter($parameter, $services);
        }
    }

    private function configureMailer(ContainerBuilder $container, array $config): void
    {
        if (false === $config['mailer']['enabled']) {
            return;
        }

        foreach ($config['mailer'] as $key => $value) {
            $container->setParameter(sprintf('%s.mailer.%s', $this->getAlias(), $key), $value);
        }
    }
}
