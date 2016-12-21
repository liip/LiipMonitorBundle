<?php

namespace Liip\MonitorBundle\Check;

use Symfony\Component\DependencyInjection\ContainerInterface;
use ZendDiagnostics\Check\CheckCollectionInterface;
use ZendDiagnostics\Check\CheckInterface;
use Doctrine\Common\Persistence\ConnectionRegistry;

/**
 * Class DoctrineMigrationsCollection
 */
class DoctrineMigrationsCollection implements CheckCollectionInterface
{
    /**
     * DI Container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Doctrine connection registry
     *
     * @var ConnectionRegistry
     */
    private $connectionRegistry;

    /**
     * Available checks
     *
     * @var CheckInterface[]
     */
    private $checks;

    /**
     * Migrations configuration
     *
     * @var array
     */
    private $migrations;

    /**
     * DoctrineMigrationsCollection constructor.
     *
     * @param ContainerInterface $container          DI container
     * @param ConnectionRegistry $connectionRegistry Connections registry
     * @param array[]            $migrations         Migrations configuration
     */
    public function __construct(
        ContainerInterface $container,
        ConnectionRegistry $connectionRegistry,
        array $migrations
    ) {
        $this->container          = $container;
        $this->connectionRegistry = $connectionRegistry;
        $this->migrations         = $migrations;
    }

    /**
     * {@inheritdoc}
     */
    public function getChecks()
    {
        if ($this->checks === null) {
            $this->checks = [];
            foreach ($this->migrations as $key => $migration) {
                if (isset($this->checks[$migration['configuration_file']])) {
                    continue;
                }

                $check = new DoctrineMigration(
                    $this->container,
                    $this->connectionRegistry,
                    $migration[ 'connection' ],
                    $migration[ 'configuration_file' ]
                );
                $check->setLabel(sprintf('Doctrine migrations "%s"', $key));

                $this->checks[$migration['configuration_file']] = $check;
            }
        }

        return $this->checks;
    }
}
