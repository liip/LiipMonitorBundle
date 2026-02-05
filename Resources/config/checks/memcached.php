<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.memcached', \Liip\MonitorBundle\Check\MemcachedCollection::class)
        ->args(['%%liip_monitor.check.memcached%%'])
        ->tag('liip_monitor.check_collection')
        ->public();
};
