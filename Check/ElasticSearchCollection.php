<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\ElasticSearch;

/**
 * @author Son Bui <sonbv00@gmail.com>
 */
class ElasticSearchCollection implements CheckCollectionInterface
{
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $name => $config) {
            if (isset($config['dsn'])) {
                $config = \array_merge($config, \parse_url($config['dsn']));
            }

            $elasticSearchUrl = sprintf('%s://%s:%s', $config['scheme'], $config['host'], $config['port']);
            $check = new ElasticSearch($elasticSearchUrl);
            $check->setLabel(\sprintf('Elastic Search "%s"', $name));

            $this->checks[\sprintf('elastic_search_%s', $name)] = $check;
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
