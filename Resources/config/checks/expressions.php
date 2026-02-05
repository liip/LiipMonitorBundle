<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.expressions', \Liip\MonitorBundle\Check\ExpressionCollection::class)
        ->args(['%%liip_monitor.check.expressions%%'])
        ->tag('liip_monitor.check_collection')
        ->public();
};
