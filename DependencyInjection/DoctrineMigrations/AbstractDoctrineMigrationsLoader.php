<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\DependencyInjection\DoctrineMigrations;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class AbstractDoctrineMigrationsLoader.
 */
abstract class AbstractDoctrineMigrationsLoader
{
    /**
     * Creates migration configuration service definition.
     *
     * @param ContainerBuilder $container      DI Container
     * @param string           $connectionName Connection name for container service
     * @param string|null      $filename       File name with migration configuration
     */
    abstract public function createMigrationConfigurationService(
        ContainerBuilder $container,
        string $connectionName,
        string $serviceId,
        ?string $filename = null
    ): void;
}
