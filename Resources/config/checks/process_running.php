<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.process_running', \Liip\MonitorBundle\Check\ProcessRunningCollection::class)
        ->public()
        ->args(['%%liip_monitor.check.process_running%%'])
        ->tag('liip_monitor.check_collection');
};
