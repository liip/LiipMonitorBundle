<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;

class SymfonyMessengerTransportCountCollection implements CheckCollectionInterface
{
    public const SERVICE_ID_PREFIX = 'messenger.transport.';

    /**
     * @var ServiceLocator
     */
    private $messengerLocator;

    /**
     * @var array
     */
    private $transports;

    public function __construct(ServiceLocator $messengerReceiverLocator, array $transportConfig)
    {
        $this->messengerLocator = $messengerReceiverLocator;
        $this->transports = $transportConfig;
    }

    public function getChecks()
    {
        $checks = [];
        foreach ($this->transports as $transportName => $config) {
            $serviceId = $config['service'] ?? self::SERVICE_ID_PREFIX.$transportName;
            if (!$this->messengerLocator->has($serviceId)) {
                throw new ServiceNotFoundException($serviceId);
            }
            $transport = $this->messengerLocator->get($serviceId);
            if (!$transport instanceof MessageCountAwareInterface) {
                throw new \Exception(sprintf('Cannot use transport "%s" for check, it does not implement MessageCountAwareInterface', $transportName));
            }
            $checks[sprintf('messenger_transport_%s', $transportName)] = new SymfonyMessengerTransportCount($transport, $transportName, $config);
        }

        return $checks;
    }
}
