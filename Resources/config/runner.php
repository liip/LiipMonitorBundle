<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('liip_monitor.runner.class', 'r');

    $services->set('liip_monitor.runner', \Liip\MonitorBundle\Runner::class)
        ->public();
};
