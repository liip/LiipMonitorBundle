<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.apc_fragmentation', \Laminas\Diagnostics\Check\ApcFragmentation::class)
        ->public()
        ->args([
            '%%liip_monitor.check.apc_fragmentation.warning%%',
            '%%liip_monitor.check.apc_fragmentation.critical%%',
        ])
        ->tag('liip_monitor.check', ['alias' => 'apc_fragmentation']);
};
