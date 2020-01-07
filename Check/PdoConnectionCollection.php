<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\PDOCheck;

class PdoConnectionCollection implements CheckCollectionInterface
{
    private $checks = [];

    public function __construct(array $connections)
    {
        foreach ($connections as $name => $connection) {
            $check = new PDOCheck($connection['dsn'], $connection['username'], $connection['password'], $connection['timeout']);
            $this->checks[sprintf('pdo_%s', $name)] = $check;
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
