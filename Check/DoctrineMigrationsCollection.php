<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\Migrations\Configuration\Configuration;
use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Check\DoctrineMigration as LaminasDoctrineMigration;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DoctrineMigrationsCollection.
 */
class DoctrineMigrationsCollection implements CheckCollectionInterface
{
    /**
     * DI Container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Available checks.
     *
     * @var CheckInterface[]
     */
    private $checks;

    /**
     * Migrations configuration service ids.
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
        $this->container = $container;
        $this->migrations = $migrations;
    }

    public function getChecks()
    {
        if (null === $this->checks) {
            $this->checks = [];
            foreach ($this->migrations as $key => $migration) {
                $check = new LaminasDoctrineMigration($this->container->get($migration));
                $check->setLabel(sprintf('Doctrine migrations "%s"', $key));

                $this->checks[$key] = $check;
            }
        }

        return $this->checks;
    }
}
