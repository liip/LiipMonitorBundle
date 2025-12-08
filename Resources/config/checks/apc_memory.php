<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.apc_memory', \Laminas\Diagnostics\Check\ApcMemory::class)
        ->public()
        ->args([
            '%%liip_monitor.check.apc_memory.warning%%',
            '%%liip_monitor.check.apc_memory.critical%%',
        ])
        ->tag('liip_monitor.check', ['alias' => 'apc_memory']);
};
