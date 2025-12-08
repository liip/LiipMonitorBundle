<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.guzzle_http_service', \Liip\MonitorBundle\Check\GuzzleHttpServiceCollection::class)
        ->public()
        ->args(['%%liip_monitor.check.guzzle_http_service%%'])
        ->tag('liip_monitor.check_collection');
};
