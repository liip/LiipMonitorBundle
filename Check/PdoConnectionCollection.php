<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckCollectionInterface;
use ZendDiagnostics\Check\PDOCheck;

class PdoConnectionCollection implements CheckCollectionInterface
{
    private $checks = array();

    public function __construct(array $connections)
    {
        foreach ($connections as $name => $connection) {
            $check = new PDOCheck($connection['dsn'], $connection['username'], $connection['password'], $connection['timeout']);
            $this->checks[sprintf('pdo_%s', $name)] = $check;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChecks()
    {
        return $this->checks;
    }
}
