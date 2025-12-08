<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.stream_wrapper_exists', \Laminas\Diagnostics\Check\StreamWrapperExists::class)
        ->public()
        ->args(['%%liip_monitor.check.stream_wrapper_exists%%'])
        ->tag('liip_monitor.check', ['alias' => 'stream_wrapper_exists']);
};
