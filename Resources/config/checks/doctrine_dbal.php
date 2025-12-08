<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.doctrine_dbal', \Liip\MonitorBundle\Check\DoctrineDbalCollection::class)
        ->public()
        ->args([
            service('doctrine'),
            '%%liip_monitor.check.doctrine_dbal%%',
        ])
        ->tag('liip_monitor.check_collection');
};
