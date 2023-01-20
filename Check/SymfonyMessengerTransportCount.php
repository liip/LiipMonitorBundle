<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;

class SymfonyMessengerTransportCount extends AbstractCheck
{
    private $transport;
    private $transportName;

    private $warningThreshold;
    private $criticalThreshold;

    public function __construct(MessageCountAwareInterface $transport, string $transportName, array $config)
    {
        $this->transport = $transport;
        $this->transportName = $transportName;

        $this->warningThreshold = $config['warning_threshold'];
        $this->criticalThreshold = $config['critical_threshold'];
    }

    public function check()
    {
        $count = $this->transport->getMessageCount();

        if ($count >= $this->criticalThreshold) {
            return new Failure(sprintf('Critical: count of messages (%d) in transport "%s" exceeds limit', $count, $this->transportName));
        }
        if ($this->warningThreshold && $count >= $this->warningThreshold) {
            return new Warning(sprintf('Warning: count of messages (%d) in transport "%s" exceeds limit', $count, $this->transportName));
        }

        return new Success(sprintf('Message count (%d) in "%s" expected range', $count, $this->transportName));
    }
}
