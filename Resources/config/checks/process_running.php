<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.process_running', \Liip\MonitorBundle\Check\ProcessRunningCollection::class)
        ->args(['%%liip_monitor.check.process_running%%'])
        ->tag('liip_monitor.check_collection')
        ->public();
};
