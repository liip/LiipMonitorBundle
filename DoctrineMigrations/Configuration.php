<?php

namespace Liip\MonitorBundle\DoctrineMigrations;

use Doctrine\Bundle\MigrationsBundle\Command\DoctrineCommand;
use Doctrine\Migrations\Configuration\Configuration as BaseConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Configuration.
 */
class Configuration extends BaseConfiguration
{
    /**
     * Flag whether doctrine migrations bundle is installed.
     *
     * @var bool
     */
    private static $haveMigrationBundle;

    /**
     * Service container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Set service container.
     *
     * @param ContainerInterface $container Service container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Tune this configuration parameters according to migrations bundle.
     */
    public function configure(): void
    {
        if (null === self::$haveMigrationBundle) {
            self::$haveMigrationBundle = class_exists(DoctrineCommand::class);
        }

        if (!self::$haveMigrationBundle) {
            return;
        }

        DoctrineCommand::configureMigrations($this->container, $this);
    }
}
