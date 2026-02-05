<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.doctrine_migrations', \Liip\MonitorBundle\Check\DoctrineMigrationsCollection::class)
        ->args([
            service('service_container'),
            '%%liip_monitor.check.doctrine_migrations%%',
        ])
        ->tag('liip_monitor.check_collection')
        ->public();
};
