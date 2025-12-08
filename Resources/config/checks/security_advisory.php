<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.security_advisory', \Laminas\Diagnostics\Check\SecurityAdvisory::class)
        ->public()
        ->args(['%%liip_monitor.check.security_advisory.lock_file%%'])
        ->tag('liip_monitor.check', ['alias' => 'security_advisory']);
};
