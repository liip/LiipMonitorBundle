<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.apc_memory', \Laminas\Diagnostics\Check\ApcMemory::class)
        ->args([
            '%%liip_monitor.check.apc_memory.warning%%',
            '%%liip_monitor.check.apc_memory.critical%%',
        ])
        ->tag('liip_monitor.check', ['alias' => 'apc_memory'])
        ->public();
};
