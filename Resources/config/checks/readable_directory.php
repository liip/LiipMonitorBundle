<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.readable_directory', \Laminas\Diagnostics\Check\DirReadable::class)
        ->args(['%%liip_monitor.check.readable_directory%%'])
        ->tag('liip_monitor.check', ['alias' => 'readable_directory'])
        ->public();
};
