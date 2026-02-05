<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.opcache_memory', \Laminas\Diagnostics\Check\OpCacheMemory::class)
        ->args([
            '%%liip_monitor.check.opcache_memory.warning%%',
            '%%liip_monitor.check.opcache_memory.critical%%',
        ])
        ->tag('liip_monitor.check', ['alias' => 'opcache_memory'])
        ->public();
};
