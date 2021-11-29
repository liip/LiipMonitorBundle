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
            $label = self::NAME_PREFIX . $name;
            $check = new ElasticSearch($config['host'], $config['port'], $config['index']);
            $check->setLabel($label);
            $checks[$label] = $check;
        }

        return $checks;
    }
}
