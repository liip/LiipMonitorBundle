<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\Memcache;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class MemcacheCollection implements CheckCollectionInterface
{
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $name => $config) {
            $check = new Memcache($config['host'], $config['port']);

            $label = $config['label'] ?? sprintf('Memcache "%s"', $name);
            $check->setLabel($label);

            $this->checks[sprintf('memcache_%s', $name)] = $check;
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
