<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.memcache', \Liip\MonitorBundle\Check\MemcacheCollection::class)
        ->args(['%%liip_monitor.check.memcache%%'])
        ->tag('liip_monitor.check_collection')
        ->public();
};
