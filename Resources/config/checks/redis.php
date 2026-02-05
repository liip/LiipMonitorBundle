<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.redis', \Liip\MonitorBundle\Check\RedisCollection::class)
        ->args(['%%liip_monitor.check.redis%%'])
        ->tag('liip_monitor.check_collection')
        ->public();
};
