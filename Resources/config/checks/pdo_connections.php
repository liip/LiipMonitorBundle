<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.pdo_connections', \Liip\MonitorBundle\Check\PdoConnectionCollection::class)
        ->public()
        ->args(['%%liip_monitor.check.pdo_connections%%'])
        ->tag('liip_monitor.check_collection');
};
