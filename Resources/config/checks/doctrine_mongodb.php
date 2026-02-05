<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.doctrine_mongodb', \Liip\MonitorBundle\Check\DoctrineMongoDbCollection::class)
        ->args([
            service('doctrine_mongodb'),
            '%%liip_monitor.check.doctrine_mongodb%%',
        ])
        ->tag('liip_monitor.check_collection')
        ->public();
};
