<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use MongoDB\Driver\Command;
use ZendDiagnostics\Check\AbstractCheck;
use ZendDiagnostics\Result\Success;

class DoctrineMongoDb extends AbstractCheck
{
    protected $manager;
    protected $connectionName;

    public function __construct(ManagerRegistry $registry, $connectionName = null)
    {
        $this->manager = $registry;
        $this->connectionName = $connectionName;
    }

    public function check()
    {
        $connection = $this->manager->getConnection($this->connectionName);

        if (\method_exists($connection, 'connect')) {
            // Using "mongo" PHP extension
            $connection->connect();

            if ($connection->isConnected()) {
                return new Success();
            }
        } else {
            // Using "mongodb" PHP extension
            $connection->getManager()->executeCommand('test', new Command(['ping' => 1]));

            return new Success();
        }

        return new Failure(
            sprintf(
                'Connection "%s" is unavailable.',
                $this->connectionName
            )
        );
    }
}
