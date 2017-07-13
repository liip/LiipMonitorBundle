<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
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
        $connection = $this->manager->getConnection();
        $connection->connect();

        return new Success();
    }
}
