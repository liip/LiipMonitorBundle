<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Liip\Monitor\EventListener\LoggingSubscriber;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.liip_monitor.logging_subscriber', LoggingSubscriber::class)
        ->args([service('logger')])
        ->tag('kernel.event_subscriber')
        ->tag('monolog.logger', ['channel' => 'health'])
    ;
};
