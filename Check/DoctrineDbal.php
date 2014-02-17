<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\Common\Persistence\ManagerRegistry;
use ZendDiagnostics\Check\AbstractCheck;
use ZendDiagnostics\Result\Success;

class DoctrineDbal extends AbstractCheck
{
    protected $manager;
    protected $connectionName;

    public function __construct(ManagerRegistry $manager, $connectionName)
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
}
