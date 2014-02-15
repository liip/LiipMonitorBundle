<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckInterface;
use PhpAmqpLib\Connection\AMQPConnection;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;

/**
 * RabbitMQCheck.
 *
 * @author Cédric Dugat <cedric@dugat.me>
 */
class RabbitMQ implements CheckInterface
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
     * @param string  $user
     * @param string  $password
     * @param string  $vhost
     */
    public function __construct(
        $host = 'localhost',
        $port = 5672,
        $user = 'guest',
        $password = 'guest',
        $vhost = '/'
    ) {
        $this->host     = $host;
        $this->port     = $port;
        $this->user     = $user;
        $this->password = $password;
        $this->vhost    = $vhost;
    }

    public function check()
    {
        if (!class_exists('PhpAmqpLib\Connection\AMQPConnection', false)) {
            return new Failure('PhpAmqpLib is not installed');
        }

        $conn = new AMQPConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost
        );

        $conn->channel();

        return new Success();
    }

    public function getLabel()
    {
        return 'RabbitMQ';
    }
}
