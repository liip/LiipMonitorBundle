<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Traversable;

class ElasticSearchCollection implements CheckCollectionInterface
{
    private const NAME_PREFIX = 'elastic_search_';
    private $configs;

    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    public function getChecks(): array
    {
        $checks = [];
        foreach ($this->configs as $name => $config) {
            $check = new ElasticSearch($config['host'], $config['port'], $config['index']);
            $check->setLabel(sprintf(
                'ElasticSearch "%s" connection',
                $name
            ));
            $checks[self::NAME_PREFIX . $name] = $check;
        }

        return $checks;
    }
}
