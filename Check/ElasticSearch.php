<?php

namespace Liip\MonitorBundle\Check;

use Exception;
use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;

class ElasticSearch extends AbstractCheck
{
    private $url;

    public function __construct(string $host, string $port, string $index)
    {
        $this->url = sprintf(
            'http://%s:%s/_cluster/health/%s',
            $host,
            $port,
            $index
        );
    }

    public function check(): ResultInterface
    {
        try {
            $elasticData = $this->executeRequest();
        } catch (Exception $exception) {
            return new Failure($exception->getMessage());
        }

        if (false === isset($elasticData['status'])) {
            return new Failure(sprintf(
                'Unexpected answer: \'%s\'.',
                json_encode($elasticData)
            ));
        }

        $message = sprintf(
            'Status: \'%s\'.',
            $elasticData['status']
        );

        if (in_array($elasticData['status'], ['green', 'yellow'])) {
            return new Success($message, $elasticData);
        }

        return new Failure($message, $elasticData);
    }

    private function executeRequest(): array
    {
        $opts = [
            'http' => [
                'method' => 'GET',
            ],
        ];

        $data = @file_get_contents($this->url, false, stream_context_create($opts));
        if (false === $data) {
            throw new Exception(sprintf('Connection to "%s" failed.', $this->url));
        }
        $array = json_decode($data, true);

        if (empty($array)) {
            throw new Exception(sprintf('Invalid response from "%s".', $this->url));
        }

        return $array;
    }
}
