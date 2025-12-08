<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.rabbit_mq', \Liip\MonitorBundle\Check\RabbitMQCollection::class)
        ->public()
        ->args(['%%liip_monitor.check.rabbit_mq%%'])
        ->tag('liip_monitor.check_collection');
};
