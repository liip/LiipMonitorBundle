<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.symfony_version', \Liip\MonitorBundle\Check\SymfonyVersion::class)
        ->public()
        ->tag('liip_monitor.check', ['alias' => 'symfony_version']);
};
