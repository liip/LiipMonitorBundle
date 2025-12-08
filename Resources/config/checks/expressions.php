<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.expressions', \Liip\MonitorBundle\Check\ExpressionCollection::class)
        ->public()
        ->args(['%%liip_monitor.check.expressions%%'])
        ->tag('liip_monitor.check_collection');
};
