<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('liip_monitor.runner.class', 'r');

    $container->services()
        ->set('liip_monitor.runner', \Liip\MonitorBundle\Runner::class)
        ->public();
};
