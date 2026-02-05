<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.symfony_requirements', \Liip\MonitorBundle\Check\SymfonyRequirements::class)
        ->args(['%%liip_monitor.check.symfony_requirements.file%%'])
        ->tag('liip_monitor.check', ['alias' => 'symfony_requirements'])
        ->public();
};
