<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.apc_fragmentation', \Laminas\Diagnostics\Check\ApcFragmentation::class)
        ->args([
            '%%liip_monitor.check.apc_fragmentation.warning%%',
            '%%liip_monitor.check.apc_fragmentation.critical%%',
        ])
        ->tag('liip_monitor.check', ['alias' => 'apc_fragmentation'])
        ->public();
};
