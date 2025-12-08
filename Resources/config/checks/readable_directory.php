<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.readable_directory', \Laminas\Diagnostics\Check\DirReadable::class)
        ->public()
        ->args(['%%liip_monitor.check.readable_directory%%'])
        ->tag('liip_monitor.check', ['alias' => 'readable_directory']);
};
