<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.class_exists', \Laminas\Diagnostics\Check\ClassExists::class)
        ->public()
        ->args(['%%liip_monitor.check.class_exists%%'])
        ->tag('liip_monitor.check', ['alias' => 'class_exists']);
};
