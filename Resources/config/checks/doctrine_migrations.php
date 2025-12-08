<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.doctrine_migrations', \Liip\MonitorBundle\Check\DoctrineMigrationsCollection::class)
        ->public()
        ->args([
            service('service_container'),
            '%%liip_monitor.check.doctrine_migrations%%',
        ])
        ->tag('liip_monitor.check_collection');
};
