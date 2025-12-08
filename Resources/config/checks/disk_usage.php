<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.disk_usage', \Laminas\Diagnostics\Check\DiskUsage::class)
        ->public()
        ->args([
            '%%liip_monitor.check.disk_usage.warning%%',
            '%%liip_monitor.check.disk_usage.critical%%',
            '%%liip_monitor.check.disk_usage.path%%',
        ])
        ->tag('liip_monitor.check', ['alias' => 'disk_usage']);
};
