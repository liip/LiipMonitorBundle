<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckCollectionInterface;
use ZendDiagnostics\Check\Redis;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RedisCollection implements CheckCollectionInterface
{
    private $checks = array();

    public function __construct(array $configs)
    {
        foreach ($configs as $name => $config) {
            $check = new Redis($config['host'], $config['port'], $config['password']);
            $check->setLabel(sprintf('Redis "%s"', $name));

            $this->checks[sprintf('redis_%s', $name)] = $check;
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
