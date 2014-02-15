<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\Common\Persistence\ManagerRegistry;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Success;

class DoctrineDbal implements CheckInterface
{
    protected $manager;
    protected $connectionName;

    public function __construct(ManagerRegistry $manager, $connectionName = null)
    {
        $this->manager = $manager;
        $this->connectionName = $connectionName;
    }

    public function check()
    {
        $connection = $this->manager->getConnection($this->connectionName);
        $connection->fetchColumn('SELECT 1');

        return new Success();
    }

    public function getLabel()
    {
        return "Doctrine DBAL Connnection";
    }
}
