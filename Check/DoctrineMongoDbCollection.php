<?php

namespace Liip\MonitorBundle\Check;

use Doctrine\Common\Persistence\ConnectionRegistry;
use ZendDiagnostics\Check\CheckCollectionInterface;

/**
 * @author Hugues Gobet <hugues.gobet@gmail.com>
 */
class DoctrineMongoDbCollection implements CheckCollectionInterface
{
    private $checks = [];

    public function __construct(ConnectionRegistry $manager, $connections)
    {
        if (!is_array($connections)) {
            $connections = [$connections];
        }

        foreach ($connections as $connection) {
            $check = new DoctrineMongoDb($manager, $connection);
            $check->setLabel(sprintf('Doctrine Mongo Db "%s" connection', $connection));

            $this->checks[sprintf('doctrine_mongodb_%s_connection', $connection)] = $check;
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
