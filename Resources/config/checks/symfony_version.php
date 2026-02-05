<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.symfony_version', \Liip\MonitorBundle\Check\SymfonyVersion::class)
        ->tag('liip_monitor.check', ['alias' => 'symfony_version'])
        ->public();
};
