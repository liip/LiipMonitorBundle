<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\Memcached;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MemcachedCollection implements CheckCollectionInterface
{
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $name => $config) {
            $check = new Memcached($config['host'], $config['port']);
            $check->setLabel(sprintf('Memcached "%s"', $name));

            $this->checks[sprintf('memcached_%s', $name)] = $check;
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
