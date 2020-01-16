<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckCollectionInterface;
use ZendDiagnostics\Check\HttpService;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class HttpServiceCollection implements CheckCollectionInterface
{
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $name => $config) {
            $check = new HttpService($config['host'], $config['port'], $config['path'], $config['status_code'], $config['content']);
            $check->setLabel(sprintf('Http Service "%s"', $name));

            $this->checks[sprintf('http_service_%s', $name)] = $check;
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
