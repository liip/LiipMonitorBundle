<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Liip\Monitor\Event\PostRunCheckSuiteEvent;
use Liip\Monitor\EventListener\MailerSubscriber;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.liip_monitor.mailer_subscriber', MailerSubscriber::class)
        ->args([service('mailer')])
        ->tag('kernel.event_listener', ['event' => PostRunCheckSuiteEvent::class, 'method' => 'afterSuite'])
    ;
};
