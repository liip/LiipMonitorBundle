<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\Bundle\MigrationsBundle\Command\DoctrineCommand;
use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\AbstractFileConfiguration;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ZendDiagnostics\Check\AbstractCheck;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Check\DoctrineMigration as ZendDoctrineMigration;
use ZendDiagnostics\Result\Failure;

/**
 * Class DoctrineMigration
 */
class DoctrineMigration extends AbstractCheck
{
    /**
     * Migration file
     *
     * @var string
     */
    private $file;

    /**
     * DB connection for migrations
     *
     * @var Connection
     */
    private $connectionName;

    /**
     * Original checker
     *
     * @var CheckInterface
     */
    private $checker;

    /**
     * ConnectionRegistry
     *
     * @var ConnectionRegistry
     */
    private $connectionRegistry;

    /**
     * DI container
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * DoctrineMigration constructor.
     *
     * @param ContainerInterface $container          Symfony DI container
     * @param ConnectionRegistry $connectionRegistry Doctrine connection registry
     * @param                    $connectionName     Connection name from doctrine config
     * @param string             $file Absolute path to migration file
     */
    public function __construct(
        ContainerInterface $container,
        ConnectionRegistry $connectionRegistry,
        $connectionName,
        $file
    ) {
        $this->container          = $container;
        $this->connectionRegistry = $connectionRegistry;
        $this->connectionName     = $connectionName;
        $this->file               = $file;
    }

    /**
     * @inheritDoc
     */
    public function check()
    {
        try {
            return $this->getDiagnostics()->check();
        } catch (\Exception $e) {
            return new Failure($e->getMessage());
        } catch (\Throwable $e) {
            return new Failure($e->getMessage());
        }
    }

    /**
     * Return Zend diagnostics object
     *
     * @return CheckInterface
     */
    private function getDiagnostics()
    {
        if (!$this->checker) {
            /** @var Connection $connection */
            $connection = $this->connectionRegistry->getConnection($this->connectionName);

            // -------
            // This part must be in sync with Doctrine\DBAL\Migrations\Tools\Console\Helper\ConfigurationHelper::loadConfig
            $map = array(
                'xml'   => '\XmlConfiguration',
                'yaml'  => '\YamlConfiguration',
                'yml'   => '\YamlConfiguration',
                'php'   => '\ArrayConfiguration',
                'json'  => '\JsonConfiguration'
            );

            $info = pathinfo($this->file);
            // check we can support this file type
            if (empty($map[$info['extension']])) {
                throw new \InvalidArgumentException('Given config file type is not supported');
            }

            $class         = 'Doctrine\DBAL\Migrations\Configuration';
            $class        .= $map[$info['extension']];
            /** @var AbstractFileConfiguration $configuration */
            $configuration = new $class($connection);
            $configuration->load($this->file);
            DoctrineCommand::configureMigrations($this->container, $configuration);
            // -------

            $configuration->validate();

            $this->checker = new ZendDoctrineMigration($configuration);
        }

        return $this->checker;
    }
}
