<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\ConnectionException;

class DoctrineMongoDb extends AbstractCheck
{
    protected $manager;
    protected $connectionName;

    public function __construct(ManagerRegistry $registry, $connectionName = null)
    {
        $this->manager = $registry;
        $this->connectionName = $connectionName;
    }

    /**
     * @return ResultInterface
     */
    public function check()
    {
        $connection = $this->manager->getConnection($this->connectionName);

        // Using "mongo" PHP extension
        if (\method_exists($connection, 'connect')) {
            $connection->connect();

            if ($connection->isConnected()) {
                return new Success();
            }

            return new Failure(
                sprintf(
                    'Connection "%s" is unavailable.',
                    $this->connectionName
                )
            );
        }

        // Using "mongodb" PHP extension
        try {
            $connection->getManager()->executeCommand('test', new Command(['ping' => 1]));
        } catch (ConnectionException $e) {
            return new Failure(
                sprintf(
                    'Connection "%s" is unavailable.',
                    $this->connectionName
                )
            );
        }

        return new Success();
    }
}
