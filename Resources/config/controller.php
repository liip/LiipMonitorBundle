<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->alias(\Liip\MonitorBundle\Controller\HealthCheckController::class, 'liip_monitor.health_controller')
        ->public();

    $services->set('liip_monitor.helper', \Liip\MonitorBundle\Helper\PathHelper::class)
        ->public()
        ->args([
            service('assets.packages'),
            service('router'),
        ]);

    $services->set('liip_monitor.health_controller', \Liip\MonitorBundle\Controller\HealthCheckController::class)
        ->public()
        ->args([
            service('liip_monitor.helper.runner_manager'),
            service('liip_monitor.helper'),
            '%liip_monitor.view_template%',
            '%liip_monitor.failure_status_code%',
        ]);
};
