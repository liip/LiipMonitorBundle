<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\Persistence\ConnectionRegistry;
use Laminas\Diagnostics\Check\CheckCollectionInterface;

/**
 * @author Hugues Gobet <hugues.gobet@gmail.com>
 */
class DoctrineMongoDbCollection implements CheckCollectionInterface
{
    private $checks = [];

    public function __construct(ConnectionRegistry $manager, $connections)
    {
        foreach ($connections as $connection) {
            $connectionName = $connection['name'];

            $check = new DoctrineDbal($manager, $connectionName);

            $label = $connection['label'] ?? sprintf('Doctrine Mongo Db "%s" connection', $connectionName);
            $check->setLabel($label);

            $this->checks[sprintf('doctrine_mongodb_%s_connection', $connectionName)] = $check;
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
