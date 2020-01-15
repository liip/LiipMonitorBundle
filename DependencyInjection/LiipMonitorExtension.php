<?php

namespace Liip\MonitorBundle\DependencyInjection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver;
use Doctrine\Migrations\Configuration\AbstractFileConfiguration;
use Doctrine\Migrations\Configuration\Configuration as DoctrineMigrationConfiguration;
use Doctrine\Migrations\MigrationException;
use Liip\MonitorBundle\DoctrineMigrations\Configuration as LiipMigrationConfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LiipMonitorExtension extends Extension implements CompilerPassInterface
{
    /**
     * Tuple (migrationsConfiguration, tempConfiguration) for doctrine migrations check.
     *
     * @var array
     */
    private $migrationConfigurationsServices = [];

    /**
     * Connection object needed for correct migration loading.
     *
     * @var Connection
     */
    private $fakeConnection;

    public function __construct()
    {
        if (class_exists(Connection::class)) {
            $this->fakeConnection = new Connection([], new Driver());
        }
    }

    /**
     * Loads the services based on your application configuration.
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('runner.xml');
        $loader->load('helper.xml');
        $loader->load('commands.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (null === $config['view_template']) {
            $config['view_template'] = __DIR__.'/../Resources/views/health/index.html.php';
        }

        if ($config['enable_controller']) {
            $container->setParameter(sprintf('%s.view_template', $this->getAlias()), $config['view_template']);
            $container->setParameter(sprintf('%s.failure_status_code', $this->getAlias()), $config['failure_status_code']);
            $loader->load('controller.xml');
        }

        $this->configureMailer($container, $loader, $config);

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

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($this->migrationConfigurationsServices as $services) {
            list($configurationService, $configuration) = $services;
            /** @var Definition $configurationService */
            /** @var DoctrineMigrationConfiguration $configuration */
            $versions = $this->getPredefinedMigrations($container, $configuration, $this->fakeConnection);
            if ($versions) {
                $configurationService->addMethodCall('registerMigrations', [$versions]);
            }
        }
    }

    /**
     * @param string $checkName
     * @param string $group
     * @param array  $values
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
                $container->setParameter($prefix.'.'.$group, $values);
                break;

            case 'symfony_version':
                break;

            case 'opcache_memory':
                if (!class_exists('ZendDiagnostics\Check\OpCacheMemory')) {
                    throw new \InvalidArgumentException('Please require at least "v1.0.4" of "ZendDiagnostics"');
                }
                break;

            case 'doctrine_migrations':
                if (!class_exists('ZendDiagnostics\Check\DoctrineMigration')) {
                    throw new \InvalidArgumentException('Please require at least "v1.0.6" of "ZendDiagnostics"');
                }

                if (!class_exists('Doctrine\Migrations\Configuration\Configuration')) {
                    throw new \InvalidArgumentException('Please require at least "v2.0.0" of "Doctrine Migrations Library"');
                }

                $container->setParameter($prefix.'.'.$group, $values);
                break;

            case 'pdo_connections':
                if (!class_exists('ZendDiagnostics\Check\PDOCheck')) {
                    throw new \InvalidArgumentException('Please require at least "v1.0.5" of "ZendDiagnostics"');
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
     *
     * @return void
     */
    private function configureDoctrineMigrationsCheck(ContainerBuilder $container, array $params)
    {
        if (!$container->hasDefinition('liip_monitor.check.doctrine_migrations') || !isset($params['groups'])) {
            return;
        }

        foreach ($params['groups'] as $groupName => $groupChecks) {
            if (!isset($groupChecks['doctrine_migrations'])) {
                continue;
            }

            $services = [];
            foreach ($groupChecks['doctrine_migrations'] as $key => $config) {
                try {
                    $serviceConfiguration =
                        $this->createMigrationConfigurationService($container, $config['connection'], $config['configuration_file'] ?? null);

                    $serviceId = sprintf('liip_monitor.check.doctrine_migrations.configuration.%s.%s', $groupName, $key);
                    $container->setDefinition($serviceId, $serviceConfiguration);

                    $services[$key] = $serviceId;
                } catch (MigrationException $e) {
                    throw new MigrationException(sprintf('Invalid doctrine migration check under "%s.%s": %s', $groupName, $key, $e->getMessage()), $e->getCode(), $e);
                }
            }

            $parameter = sprintf('%s.check.%s.%s', $this->getAlias(), 'doctrine_migrations', $groupName);
            $container->setParameter($parameter, $services);
        }
    }

    private function configureMailer(ContainerBuilder $container, LoaderInterface $loader, array $config)
    {
        if (false === $config['mailer']['enabled']) {
            $config['mailer'] = [
                'enabled' => false,
            ];
        }

        foreach ($config['mailer'] as $key => $value) {
            $container->setParameter(sprintf('%s.mailer.%s', $this->getAlias(), $key), $value);
        }
    }

    /**
     * Return key-value array with migration version as key and class as a value defined in config file.
     *
     * @param ContainerBuilder               $container  The container
     * @param DoctrineMigrationConfiguration $config     Current configuration
     * @param Connection                     $connection Fake connections
     *
     * @return array[]
     */
    private function getPredefinedMigrations(ContainerBuilder $container, DoctrineMigrationConfiguration $config, Connection $connection)
    {
        $result = [];

        $diff = new LiipMigrationConfiguration($connection);

        if ($namespace = $config->getMigrationsNamespace()) {
            $diff->setMigrationsNamespace($config->getMigrationsNamespace());
        }

        if ($dir = $config->getMigrationsDirectory()) {
            $diff->setMigrationsDirectory($dir);
        }

        $diff->setContainer($container);
        $diff->configure();

        foreach ($config->getMigrations() as $version) {
            $result[$version->getVersion()] = get_class($version->getMigration());
        }

        foreach ($diff->getAvailableVersions() as $version) {
            unset($result[$version]);
        }

        return $result;
    }

    /**
     * Creates migration configuration service definition.
     *
     * @param ContainerBuilder $container      DI Container
     * @param string           $connectionName Connection name for container service
     * @param string           $filename       File name with migration configuration
     *
     * @return DefinitionDecorator|ChildDefinition
     */
    private function createMigrationConfigurationService(ContainerBuilder $container, string $connectionName, string $filename = null)
    {
        $configuration = $this->createTemporaryConfiguration($container, $this->fakeConnection, $filename);

        $configurationServiceName = 'liip_monitor.check.doctrine_migrations.abstract_configuration';
        $serviceConfiguration = class_exists('Symfony\Component\DependencyInjection\ChildDefinition')
            ? new ChildDefinition($configurationServiceName)
            : new DefinitionDecorator($configurationServiceName)
        ;

        $this->migrationConfigurationsServices[] = [$serviceConfiguration, $configuration];

        $serviceConfiguration->replaceArgument(
            0,
            new Reference(sprintf('doctrine.dbal.%s_connection', $connectionName))
        );

        if ($configuration->getMigrationsNamespace()) {
            $serviceConfiguration->addMethodCall(
                'setMigrationsNamespace',
                [$configuration->getMigrationsNamespace()]
            );
        }

        if ($configuration->getMigrationsTableName()) {
            $serviceConfiguration->addMethodCall(
                'setMigrationsTableName',
                [$configuration->getMigrationsTableName()]
            );
        }

        if ($configuration->getMigrationsColumnName()) {
            $serviceConfiguration->addMethodCall(
                'setMigrationsColumnName',
                [$configuration->getMigrationsColumnName()]
            );
        }

        if ($configuration->getName()) {
            $serviceConfiguration->addMethodCall('setName', [$configuration->getName()]);
        }

        if ($configuration->getMigrationsDirectory()) {
            $directory = $configuration->getMigrationsDirectory();
            $pathPlaceholders = ['kernel.root_dir', 'kernel.cache_dir', 'kernel.logs_dir'];
            foreach ($pathPlaceholders as $parameter) {
                $kernelDir = realpath($container->getParameter($parameter));
                if (0 === strpos(realpath($directory), $kernelDir)) {
                    $directory = str_replace($kernelDir, "%{$parameter}%", $directory);
                    break;
                }
            }

            $serviceConfiguration->addMethodCall(
                'setMigrationsDirectory',
                [$directory]
            );
        }

        $serviceConfiguration->addMethodCall('configure', []);

        if ($configuration->areMigrationsOrganizedByYear()) {
            $serviceConfiguration->addMethodCall('setMigrationsAreOrganizedByYear', [true]);

            return $serviceConfiguration;
        } elseif ($configuration->areMigrationsOrganizedByYearAndMonth()) {
            $serviceConfiguration->addMethodCall('setMigrationsAreOrganizedByYearAndMonth', [true]);

            return $serviceConfiguration;
        }

        return $serviceConfiguration;
    }

    /**
     * Creates in-memory migration configuration for setting up container service.
     *
     * @param ContainerBuilder $container  The container
     * @param Connection       $connection Fake connection
     * @param string           $filename   Migrations configuration file
     */
    private function createTemporaryConfiguration(
        ContainerBuilder $container,
        Connection $connection,
        string $filename = null
    ): DoctrineMigrationConfiguration {
        if (null === $filename) {
            // this is configured from migrations bundle
            return new DoctrineMigrationConfiguration($connection);
        }

        // -------
        // This part must be in sync with Doctrine\Migrations\Tools\Console\Helper\ConfigurationHelper::loadConfig
        $map = [
            'xml' => '\XmlConfiguration',
            'yaml' => '\YamlConfiguration',
            'yml' => '\YamlConfiguration',
            'php' => '\ArrayConfiguration',
            'json' => '\JsonConfiguration',
        ];
        // --------

        $filename = $container->getParameterBag()->resolveValue($filename);
        $info = pathinfo($filename);
        // check we can support this file type
        if (empty($map[$info['extension']])) {
            throw new \InvalidArgumentException('Given config file type is not supported');
        }

        $class = 'Doctrine\Migrations\Configuration';
        $class .= $map[$info['extension']];
        // -------

        /** @var AbstractFileConfiguration $configuration */
        $configuration = new $class($connection);
        $configuration->load($filename);
        $configuration->validate();

        return $configuration;
    }
}
