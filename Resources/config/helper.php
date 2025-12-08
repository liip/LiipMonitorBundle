<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.helper.raw_console_reporter', \Liip\MonitorBundle\Helper\RawConsoleReporter::class)
        ->public();

    $services->set('liip_monitor.helper.console_reporter', \Liip\MonitorBundle\Helper\ConsoleReporter::class)
        ->public();

    $services->set('liip_monitor.helper.runner_manager', \Liip\MonitorBundle\Helper\RunnerManager::class)
        ->public()
        ->args([service('service_container')]);
};
