<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('liip_monitor.helper.raw_console_reporter', \Liip\MonitorBundle\Helper\RawConsoleReporter::class)
        ->public();

    $services->set('liip_monitor.helper.console_reporter', \Liip\MonitorBundle\Helper\ConsoleReporter::class)
        ->public();

    $services->set('liip_monitor.helper.runner_manager', \Liip\MonitorBundle\Helper\RunnerManager::class)
        ->args([service('service_container')])
        ->public();
};
