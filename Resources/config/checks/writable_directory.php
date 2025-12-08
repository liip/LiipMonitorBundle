<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.writable_directory', \Laminas\Diagnostics\Check\DirWritable::class)
        ->public()
        ->args(['%%liip_monitor.check.writable_directory%%'])
        ->tag('liip_monitor.check', ['alias' => 'writable_directory']);
};
