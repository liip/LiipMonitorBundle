<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;
use Doctrine\Persistence\ConnectionRegistry;
use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\ResultInterface;
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

    /**
     * @return ResultInterface
     */
    public function check()
    {
        $connection = $this->manager->getConnection($this->connectionName);

        if ($connection instanceof PrimaryReadReplicaConnection) {
            $connection->ensureConnectedToReplica();
            $this->runCheck($connection);
            $connection->ensureConnectedToPrimary();
            $this->runCheck($connection);
        } else {
            $this->runCheck($connection);
        }

        return new Success();
    }

    private function runCheck($connection)
    {
        $query = $connection->getDriver()->getDatabasePlatform($connection)->getDummySelectSQL();

        // after dbal 2.11 fetchOne replace fetchColumn
        if (method_exists($connection, 'fetchOne')) {
            $connection->fetchOne($query);
        } else {
            $connection->fetchColumn($query);
        }
    }
}
