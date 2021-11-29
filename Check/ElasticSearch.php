<?php

namespace Liip\MonitorBundle\Check;

use Exception;
use InvalidArgumentException;
use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

class ElasticSearch implements CheckInterface
{
    private const CHECK_NAME = 'ElasticSearch';
    private const URI = '/_cluster/health/';

    private $label;
    private $url;

    public function __construct(array $config)
    {
        $this->url = $this->buildUrl($config);
        $this->label = (sprintf('%s "%s"', self::CHECK_NAME, $this->url));
    }

    public function check(): ResultInterface
    {
        $elasticData = $this->executeRequest();

        if (false === isset($elasticData['status'])) {
            return new Failure(sprintf(
                'Unexpected answer: \'%s\'.',
                json_encode($elasticData)
            ));
        }

        $message = sprintf(
            'Status: \'%s\'. Report: \'%s\'.',
            $elasticData['status'],
            json_encode($elasticData)
        );

        if ('green' === $elasticData['status']) {
            return new Success($message);
        }

        return new Failure($message);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    private function buildUrl(array $config): string
    {
        if (!isset($config['host']) && !isset($config['port']) && !isset($config['index'])) {
            throw new InvalidArgumentException(sprintf(
                'These parameters are required. Got - host:\'%s\', port:\'%s\', index:\'%s\'.',
                $config['host'] ?? '',
                $config['port'] ?? '',
                $config['index'] ?? ''
            ));
        }

        return 'http://' . $config['host'] . ':' . $config['port'] . self::URI . $config['index'];
    }

    private function executeRequest(): array
    {
        $opts = [
            'http' => [
                'method' => 'GET',
            ],
        ];

        $array = json_decode(file_get_contents($this->url, false, stream_context_create($opts)), true);

        if (empty($array)) {
            throw new Exception(sprintf('Invalid response from "%s".', $this->url));
        }

        return $array;
    }

}