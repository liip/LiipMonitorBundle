<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.class_exists', \Laminas\Diagnostics\Check\ClassExists::class)
        ->args(['%%liip_monitor.check.class_exists%%'])
        ->tag('liip_monitor.check', ['alias' => 'class_exists'])
        ->public();
};
