<?php

namespace Liip\MonitorBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\Configuration\Configuration as BaseConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Bundle\MigrationsBundle\Command\DoctrineCommand;

/**
 * Class Configuration
 */
class Configuration extends BaseConfiguration
{
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
        DoctrineCommand::configureMigrations($this->container, $this);
    }
}
