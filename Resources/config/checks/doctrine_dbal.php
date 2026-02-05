<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.doctrine_dbal', \Liip\MonitorBundle\Check\DoctrineDbalCollection::class)
        ->args([
            service('doctrine'),
            '%%liip_monitor.check.doctrine_dbal%%',
        ])
        ->tag('liip_monitor.check_collection')
        ->public();
};
