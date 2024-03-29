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
        if (!is_array($connections)) {
            $connections = [$connections];
        }

        foreach ($connections as $connection) {
            $check = new DoctrineDbal($manager, $connection);
            $check->setLabel(sprintf('Doctrine DBAL "%s" connection', $connection));

            $this->checks[sprintf('doctrine_dbal_%s_connection', $connection)] = $check;
        }
    }

    /**
     * @return array|\Traversable
     */
    public function getChecks()
    {
        return $this->checks;
    }
}
