<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.security_advisory', \Laminas\Diagnostics\Check\SecurityAdvisory::class)
        ->args(['%%liip_monitor.check.security_advisory.lock_file%%'])
        ->tag('liip_monitor.check', ['alias' => 'security_advisory'])
        ->public();
};
