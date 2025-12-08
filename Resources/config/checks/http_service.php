<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.http_service', \Liip\MonitorBundle\Check\HttpServiceCollection::class)
        ->public()
        ->args(['%%liip_monitor.check.http_service%%'])
        ->tag('liip_monitor.check_collection');
};
