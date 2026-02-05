<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('liip_monitor.health_check.command', \Liip\MonitorBundle\Command\HealthCheckCommand::class)
        ->args([
            service('liip_monitor.helper.console_reporter'),
            service('liip_monitor.helper.raw_console_reporter'),
            service('liip_monitor.helper.runner_manager'),
        ])
        ->tag('console.command', ['command' => 'monitor:health'])
        ->public();

    $services->set('liip_monitor.list_checks.command', \Liip\MonitorBundle\Command\ListChecksCommand::class)
        ->args([
            service('liip_monitor.helper.runner_manager'),
            service('liip_monitor.runner'),
        ])
        ->tag('console.command', ['command' => 'monitor:list'])
        ->public();
};
