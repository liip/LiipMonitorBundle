<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.opcache_memory', \Laminas\Diagnostics\Check\OpCacheMemory::class)
        ->public()
        ->args([
            '%%liip_monitor.check.opcache_memory.warning%%',
            '%%liip_monitor.check.opcache_memory.critical%%',
        ])
        ->tag('liip_monitor.check', ['alias' => 'opcache_memory']);
};
