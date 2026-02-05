<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.pdo_connections', \Liip\MonitorBundle\Check\PdoConnectionCollection::class)
        ->args(['%%liip_monitor.check.pdo_connections%%'])
        ->tag('liip_monitor.check_collection')
        ->public();
};
