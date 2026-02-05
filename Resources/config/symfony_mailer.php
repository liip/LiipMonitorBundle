<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.reporter.symfony_mailer', \Liip\MonitorBundle\Helper\SymfonyMailerReporter::class)
        ->args([
            service('mailer'),
            param('liip_monitor.mailer.recipient'),
            param('liip_monitor.mailer.sender'),
            param('liip_monitor.mailer.subject'),
            param('liip_monitor.mailer.send_on_warning'),
        ])
        ->tag('liip_monitor.additional_reporter', ['alias' => 'symfony_mailer']);
};
