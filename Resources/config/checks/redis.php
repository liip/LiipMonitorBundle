<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.redis', \Liip\MonitorBundle\Check\RedisCollection::class)
        ->public()
        ->args(['%%liip_monitor.check.redis%%'])
        ->tag('liip_monitor.check_collection');
};
