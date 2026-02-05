<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.cpu_performance', \Laminas\Diagnostics\Check\CpuPerformance::class)
        ->args(['%%liip_monitor.check.cpu_performance%%'])
        ->tag('liip_monitor.check', ['alias' => 'cpu_performance'])
        ->public();
};
