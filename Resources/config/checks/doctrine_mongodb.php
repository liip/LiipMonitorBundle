<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.doctrine_mongodb', \Liip\MonitorBundle\Check\DoctrineMongoDbCollection::class)
        ->public()
        ->args([
            service('doctrine_mongodb'),
            '%%liip_monitor.check.doctrine_mongodb%%',
        ])
        ->tag('liip_monitor.check_collection');
};
