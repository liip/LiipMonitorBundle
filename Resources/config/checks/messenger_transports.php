<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.messenger_transports', \Liip\MonitorBundle\Check\SymfonyMessengerTransportCountCollection::class)
        ->public()
        ->args([
            service('messenger.receiver_locator'),
            '%%liip_monitor.check.messenger_transports%%',
        ])
        ->tag('liip_monitor.check_collection');
};
