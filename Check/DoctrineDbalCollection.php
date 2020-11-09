<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\Persistence\ConnectionRegistry;
use Laminas\Diagnostics\Check\CheckCollectionInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class DoctrineDbalCollection implements CheckCollectionInterface
{
    private $checks = [];

    public function __construct(ConnectionRegistry $manager, $connections)
    {
        foreach ($connections as $connection) {
            $connectionName = $connection['name'];

            $check = new DoctrineDbal($manager, $connectionName);

            $label = $connection['label'] ?? sprintf('Doctrine DBAL "%s" connection', $connectionName);
            $check->setLabel($label);

            $this->checks[sprintf('doctrine_dbal_%s_connection', $connectionName)] = $check;
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
