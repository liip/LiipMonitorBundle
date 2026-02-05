<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.stream_wrapper_exists', \Laminas\Diagnostics\Check\StreamWrapperExists::class)
        ->args(['%%liip_monitor.check.stream_wrapper_exists%%'])
        ->tag('liip_monitor.check', ['alias' => 'stream_wrapper_exists'])
        ->public();
};
