<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckCollectionInterface;
use ZendDiagnostics\Check\Memcache;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MemcacheCollection implements CheckCollectionInterface
{
    private $checks = array();

    public function __construct(array $configs)
    {
        foreach ($configs as $name => $config) {
            $check = new Memcache($config['host'], $config['port']);
            $check->setLabel(sprintf('Memcache "%s"', $name));

            $this->checks[sprintf('memcache_%s', $name)] = $check;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getChecks()
    {
        return $this->checks;
    }
}
