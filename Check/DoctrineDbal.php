<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\Persistence\ConnectionRegistry;
use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Success;

class DoctrineDbal extends AbstractCheck
{
    protected $manager;
    protected $connectionName;

    public function __construct(ConnectionRegistry $registry, $connectionName = null)
    {
        $this->manager = $registry;
        $this->connectionName = $connectionName;
    }

    public function check()
    {
        $connection = $this->manager->getConnection($this->connectionName);
        $query = $connection->getDriver()->getDatabasePlatform()->getDummySelectSQL();

        // after dbal 2.11 fetchOne replace fetchColumn
        if (method_exists($connection, 'fetchOne')) {
            $connection->fetchOne($query);
        } else {
            $connection->fetchColumn($query);
        }

        return new Success();
    }
}
