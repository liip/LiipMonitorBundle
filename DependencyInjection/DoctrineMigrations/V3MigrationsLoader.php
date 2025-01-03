<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\DependencyInjection\DoctrineMigrations;

use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\JsonFile;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Configuration\Migration\XmlFile;
use Doctrine\Migrations\Configuration\Migration\YamlFile;
use Doctrine\Migrations\DependencyFactory;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class V3MigrationsLoader.
 */
final class V3MigrationsLoader extends AbstractDoctrineMigrationsLoader
{
    public function createMigrationConfigurationService(
        ContainerBuilder $container,
        string $connectionName,
        string $serviceId,
        ?string $filename = null
    ): void {
        if (null !== $filename) {
            $configurationClass = $this->getConfigurationLoaderClass($container, $filename);
            $filenameHash = md5($filename);
            $configurationServiceId = 'liip_monitor.check.doctrine_migrations.configuration.'.$filenameHash;
            if (!$container->has($configurationServiceId)) {
                $container->register($configurationServiceId, $configurationClass)
                          ->setPublic(false)
                          ->setArguments([$filename]);
            }

            $connectionLoaderId = 'liip_monitor.check.doctrine_migrations.connection_loader.'.$connectionName;
            if (!$container->has($connectionLoaderId)) {
                $container->register($connectionLoaderId, ExistingConnection::class)
                          ->setPublic(false)
                          ->setArguments([new Reference(sprintf('doctrine.dbal.%s_connection', $connectionName))]);
            }

            $dependencyFactoryId = sprintf(
                'liip_monitor.check.doctrine_migrations.dependency_factory.%s.%s',
                $connectionName,
                $filenameHash
            );
            if (!$container->has($dependencyFactoryId)) {
                $container->register($dependencyFactoryId, DependencyFactory::class)
                          ->setFactory([DependencyFactory::class, 'fromConnection'])
                          ->setPublic(false)
                          ->setArguments([new Reference($configurationServiceId), new Reference($connectionLoaderId)]);
            }

            $container->setAlias($serviceId, new Alias($dependencyFactoryId, true));

            return;
        }

        $container->setAlias($serviceId, new Alias('doctrine.migrations.dependency_factory', true));
    }

    /**
     * Creates in-memory migration configuration for setting up container service.
     *
     * @param ContainerBuilder $container The container
     * @param string           $filename  Migrations configuration file
     *
     * @return string FQCN of configuration class loader
     */
    private function getConfigurationLoaderClass(ContainerBuilder $container, string $filename): string
    {
        // Available options are located under Doctrine\Migrations\Configuration\Migration namespace
        $map = [
            'xml' => XmlFile::class,
            'yaml' => YamlFile::class,
            'yml' => YamlFile::class,
            'php' => PhpFile::class,
            'json' => JsonFile::class,
        ];

        $filename = $container->getParameterBag()->resolveValue($filename);
        $info = pathinfo($filename);

        $extension = $info['extension'] ?? '';
        if (empty($map[$extension])) {
            throw new \InvalidArgumentException(sprintf('Config file type "%s" is not supported', $extension));
        }

        return $map[$info['extension']];
    }
}
