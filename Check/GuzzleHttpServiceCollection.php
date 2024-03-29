<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\GuzzleHttpService;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class GuzzleHttpServiceCollection implements CheckCollectionInterface
{
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $name => $config) {
            $check = new GuzzleHttpService(
                $config['url'],
                $config['headers'],
                $config['options'],
                $config['status_code'],
                $config['content'],
                null,
                $config['method'],
                $config['body']
            );
            $check->setLabel(sprintf('Guzzle Http Service "%s"', $name));

            $this->checks[sprintf('guzzle_http_service_%s', $name)] = $check;
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
