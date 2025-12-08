<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.memcached', \Liip\MonitorBundle\Check\MemcachedCollection::class)
        ->public()
        ->args(['%%liip_monitor.check.memcached%%'])
        ->tag('liip_monitor.check_collection');
};
