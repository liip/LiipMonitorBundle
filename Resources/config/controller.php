<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(\Liip\MonitorBundle\Controller\HealthCheckController::class, 'liip_monitor.health_controller')
        ->public();

    $services->set('liip_monitor.helper', \Liip\MonitorBundle\Helper\PathHelper::class)
        ->args([
            service('assets.packages'),
            service('router'),
        ])
        ->public();

    $services->set('liip_monitor.health_controller', \Liip\MonitorBundle\Controller\HealthCheckController::class)
        ->args([
            service('liip_monitor.helper.runner_manager'),
            service('liip_monitor.helper'),
            param('liip_monitor.view_template'),
            param('liip_monitor.failure_status_code'),
        ])
        ->public();
};
