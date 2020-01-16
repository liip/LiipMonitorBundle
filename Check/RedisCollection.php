<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckCollectionInterface;
use ZendDiagnostics\Check\Redis;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RedisCollection implements CheckCollectionInterface
{
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $name => $config) {
            if (isset($config['dsn'])) {
                $this->parseDsn($config);
            }

            $check = new Redis($config['host'], $config['port'], $config['password']);
            $check->setLabel(\sprintf('Redis "%s"', $name));

            $this->checks[\sprintf('redis_%s', $name)] = $check;
        }
    }

    private function parseDsn(array &$config)
    {
        $config = \array_merge($config, \parse_url($config['dsn']));

        if (isset($config['pass'])) {
            $config['password'] = $config['pass'];
            // Cleanup
            unset($config['pass']);
        } elseif (isset($config['user'])) {
            /*
             * since "redis://my-super-secret-password@redis-host:6379" is a valid redis
             * dsn but \parse_url does not understand this notation and extracts the auth as user,
             * we need to check for it.
             */
            $config['password'] = $config['user'];
            unset($config['user']);
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
