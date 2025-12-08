<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.cpu_performance', \Laminas\Diagnostics\Check\CpuPerformance::class)
        ->public()
        ->args(['%%liip_monitor.check.cpu_performance%%'])
        ->tag('liip_monitor.check', ['alias' => 'cpu_performance']);
};
