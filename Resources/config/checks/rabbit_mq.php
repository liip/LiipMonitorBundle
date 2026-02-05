<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.rabbit_mq', \Liip\MonitorBundle\Check\RabbitMQCollection::class)
        ->args(['%%liip_monitor.check.rabbit_mq%%'])
        ->tag('liip_monitor.check_collection')
        ->public();
};
