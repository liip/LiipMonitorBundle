<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.memcache', \Liip\MonitorBundle\Check\MemcacheCollection::class)
        ->public()
        ->args(['%%liip_monitor.check.memcache%%'])
        ->tag('liip_monitor.check_collection');
};
