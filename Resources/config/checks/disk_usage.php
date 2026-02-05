<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.disk_usage', \Laminas\Diagnostics\Check\DiskUsage::class)
        ->args([
            '%%liip_monitor.check.disk_usage.warning%%',
            '%%liip_monitor.check.disk_usage.critical%%',
            '%%liip_monitor.check.disk_usage.path%%',
        ])
        ->tag('liip_monitor.check', ['alias' => 'disk_usage'])
        ->public();
};
