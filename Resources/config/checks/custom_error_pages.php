<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.custom_error_pages', \Liip\MonitorBundle\Check\CustomErrorPages::class)
        ->public()
        ->args([
            '%%liip_monitor.check.custom_error_pages.error_codes%%',
            '%%liip_monitor.check.custom_error_pages.path%%',
            '%kernel.project_dir%',
        ])
        ->tag('liip_monitor.check', ['alias' => 'custom_error_pages']);
};
