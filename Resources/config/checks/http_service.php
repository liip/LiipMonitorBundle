<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.http_service', \Liip\MonitorBundle\Check\HttpServiceCollection::class)
        ->args(['%%liip_monitor.check.http_service%%'])
        ->tag('liip_monitor.check_collection')
        ->public();
};
