<?php

namespace Liip\MonitorBundle\DoctrineMigrations;

use Doctrine\Migrations\Configuration\Configuration as BaseConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Bundle\MigrationsBundle\Command\DoctrineCommand;

/**
 * Class Configuration
 */
class Configuration extends BaseConfiguration
{
    /**
     * Flag whether doctrine migrations bundle is installed
     *
     * @var bool
     */
    private static $haveMigrationBundle;

    /**
     * Service container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Set service container
     *
     * @param ContainerInterface $container Service container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Tune this configuration parameters according to migrations bundle
     *
     * @return void
     */
    public function configure()
    {
        if (self::$haveMigrationBundle === null) {
            self::$haveMigrationBundle = class_exists(DoctrineCommand::class);
        }

        if (!self::$haveMigrationBundle) {
            return;
        }

        DoctrineCommand::configureMigrations($this->container, $this);
    }
}
