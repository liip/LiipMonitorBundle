<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.messenger_transports', \Liip\MonitorBundle\Check\SymfonyMessengerTransportCountCollection::class)
        ->args([
            service('messenger.receiver_locator'),
            '%%liip_monitor.check.messenger_transports%%',
        ])
        ->tag('liip_monitor.check_collection')
        ->public();
};
