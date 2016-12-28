<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ZendDiagnostics\Check\CheckCollectionInterface;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Check\DoctrineMigration as ZendDoctrineMigration;

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
     * Available checks
     *
     * @var CheckInterface[]
     */
    private $checks;

    /**
     * Migrations configuration service ids
     *
     * @var string[]
     */
    private $migrations;

    /**
     * DoctrineMigrationsCollection constructor.
     *
     * @param ContainerInterface $container  DI container
     * @param Configuration[]    $migrations Migrations configuration service ids
     */
    public function __construct(
        ContainerInterface $container,
        array $migrations
    ) {
        $this->container  = $container;
        $this->migrations = $migrations;
    }

    /**
     * {@inheritdoc}
     */
    public function getChecks()
    {
        if ($this->checks === null) {
            $this->checks = [];
            foreach ($this->migrations as $key => $migration) {
                $check = new ZendDoctrineMigration($this->container->get($migration));
                $check->setLabel(sprintf('Doctrine migrations "%s"', $key));

                $this->checks[$key] = $check;
            }
        }

        return $this->checks;
    }
}
