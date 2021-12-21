<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\DependencyInjection\DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\SQLite\Driver;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as LegacyDriver;
use Doctrine\Migrations\Configuration\AbstractFileConfiguration;
use Doctrine\Migrations\Configuration\Configuration as DoctrineMigrationConfiguration;
use Liip\MonitorBundle\DoctrineMigrations\Configuration;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class V2MigrationsLoader.
 */
final class V2MigrationsLoader extends AbstractDoctrineMigrationsLoader implements CompilerPassInterface
{
    /**
     * Connection object needed for correct migration loading.
     *
     * @var Connection
     */
    private $fakeConnection;

    /**
     * Tuple (migrationsConfiguration, tempConfiguration) for doctrine migrations check.
     *
     * @var array
     */
    private $migrationConfigurationsServices = [];

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->migrationConfigurationsServices as $services) {
            [$configurationService, $configuration] = $services;
            /** @var Definition $configurationService */
            /** @var DoctrineMigrationConfiguration $configuration */
            $versions = $this->getPredefinedMigrations($container, $configuration, $this->fakeConnection);
            if ($versions) {
                $configurationService->addMethodCall('registerMigrations', [$versions]);
            }
        }
    }

    public function createMigrationConfigurationService(
        ContainerBuilder $container,
        string $connectionName,
        string $serviceId,
        string $filename = null
    ): void {
        if (!$container->has(Configuration::class)) {
            $container->register(Configuration::class)
                ->setAbstract(true)
                ->setPublic(true)
                ->setArguments([null])
                ->addMethodCall('setContainer', [new Reference('service_container')]);
        }

        $configuration = $this->createTemporaryConfiguration($container, $this->getConnection(), $filename);

        $serviceConfiguration = new ChildDefinition(Configuration::class);

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
        } elseif ($configuration->areMigrationsOrganizedByYearAndMonth()) {
            $serviceConfiguration->addMethodCall('setMigrationsAreOrganizedByYearAndMonth', [true]);
        }

        $container->setDefinition($serviceId, $serviceConfiguration);
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

    private function getConnection(): Connection
    {
        if (null === $this->fakeConnection) {
            if (!class_exists(Connection::class)) {
                throw new \InvalidArgumentException(sprintf('Can not configure doctrine migration checks, because of absence of "%s" class', Connection::class));
            }

            $driver = class_exists(Driver::class)
                ? new Driver()
                : new LegacyDriver();

            $this->fakeConnection = new Connection([], $driver);
        }

        return $this->fakeConnection;
    }

    /**
     * Return key-value array with migration version as key and class as a value defined in config file.
     *
     * @param ContainerBuilder               $container  The container
     * @param DoctrineMigrationConfiguration $config     Current configuration
     * @param Connection                     $connection Fake connections
     *
     * @return string[]
     */
    private function getPredefinedMigrations(ContainerBuilder $container, DoctrineMigrationConfiguration $config, Connection $connection): array
    {
        $result = [];

        $diff = new Configuration($connection);

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
}
