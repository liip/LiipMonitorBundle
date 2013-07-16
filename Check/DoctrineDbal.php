<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\DBAL\Connection;

use ZendDiagnostics\Check\AbstractCheck;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;

class DoctrineDbal extends AbstractCheck
{
    /**
     * @var ConnectionRegistry
     */
    protected $manager;

    /**
     * @var string
     */
    protected $connectionName;

    public function __construct(ConnectionRegistry $manager, $connectionName = 'default')
    {
        $this->manager = $manager;
        $this->connectionName = $connectionName;
    }

    public function check()
    {
        try {
            $connection = $this->manager->getConnection($this->connectionName);
            $connection->fetchColumn('SELECT 1');
        } catch (\Exception $e) {
            return new Failure($e->getMessage());
        }

        return new Success();
    }

    public function getName()
    {
        return "Doctrine DBAL Connnection";
    }
}