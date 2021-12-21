<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\DependencyInjection\DoctrineMigrations;

use Doctrine\Bundle\MigrationsBundle\Command\DoctrineCommand;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class DoctrineMigrationsLoader.
 */
final class DoctrineMigrationsLoader implements CompilerPassInterface
{
    /**
     * @var AbstractDoctrineMigrationsLoader
     */
    private $loader;

    /**
     * DoctrineMigrationsLoader constructor.
     */
    public function __construct()
    {
        $this->loader = class_exists(DoctrineCommand::class)
            ? new V2MigrationsLoader()
            : new V3MigrationsLoader();
    }

    public function process(ContainerBuilder $container): void
    {
        if (!($this->loader instanceof CompilerPassInterface)) {
            return;
        }

        $this->loader->process($container);
    }

    public function loadMigrationChecks(ContainerBuilder $container, array $migrationChecksConfig, string $groupName): array
    {
        $services = [];
        foreach ($migrationChecksConfig as $key => $config) {
            try {
                $serviceId = sprintf('liip_monitor.check.doctrine_migrations.configuration.%s.%s', $groupName, $key);
                $this->loader->createMigrationConfigurationService(
                    $container,
                    $config['connection'],
                    $serviceId,
                    $config['configuration_file'] ?? null
                );

                $services[$key] = $serviceId;
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf('Invalid doctrine migration check under "%s.%s": %s', $groupName, $key, $e->getMessage()), $e->getCode(), $e);
            }
        }

        return $services;
    }
}
