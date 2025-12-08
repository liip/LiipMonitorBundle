<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.symfony_requirements', \Liip\MonitorBundle\Check\SymfonyRequirements::class)
        ->public()
        ->args(['%%liip_monitor.check.symfony_requirements.file%%'])
        ->tag('liip_monitor.check', ['alias' => 'symfony_requirements']);
};
