<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckCollectionInterface;
use ZendDiagnostics\Check\RabbitMQ;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RabbitMQCollection implements CheckCollectionInterface
{
    private $checks = array();

    public function __construct(array $configs)
    {
        foreach ($configs as $name => $config) {
            $check = new RabbitMQ($config['host'], $config['port'], $config['user'], $config['password'], $config['vhost']);
            $check->setLabel(sprintf('Rabbit MQ "%s"', $name));

            $this->checks[sprintf('rabbit_mq_%s', $name)] = $check;
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
