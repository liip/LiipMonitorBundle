<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.custom_error_pages', \Liip\MonitorBundle\Check\CustomErrorPages::class)
        ->args([
            '%%liip_monitor.check.custom_error_pages.error_codes%%',
            '%%liip_monitor.check.custom_error_pages.path%%',
            param('kernel.project_dir'),
        ])
        ->tag('liip_monitor.check', ['alias' => 'custom_error_pages'])
        ->public();
};
