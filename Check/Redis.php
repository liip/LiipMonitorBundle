<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\AbstractCheck;
use Predis\Client;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;

/**
 * @author Cédric Dugat <cedric@dugat.me>
 */
class Redis extends AbstractCheck
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var integer
     */
    protected $port;

    /**
     * Construct.
     *
     * @param string  $host
     * @param integer $port
     */
    public function __construct($host = 'localhost', $port = 6379)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function check()
    {
        if (!class_exists('Predis\Client', false)) {
            return new Failure('Predis is not installed');
        }

        $client = new Client(array(
            'host' => $this->host,
            'port' => $this->port,
        ));

        if (!$client->ping()) {
            return new Failure(
                sprintf(
                    'No Redis server running at host %s on port %s',
                    $this->host,
                    $this->port
                )
            );
        }

        return new Success();
    }
}
