<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.writable_directory', \Laminas\Diagnostics\Check\DirWritable::class)
        ->args(['%%liip_monitor.check.writable_directory%%'])
        ->tag('liip_monitor.check', ['alias' => 'writable_directory'])
        ->public();
};
