<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.reporter.symfony_mailer', \Liip\MonitorBundle\Helper\SymfonyMailerReporter::class)
        ->args([
            service('mailer'),
            '%liip_monitor.mailer.recipient%',
            '%liip_monitor.mailer.sender%',
            '%liip_monitor.mailer.subject%',
            '%liip_monitor.mailer.send_on_warning%',
        ])
        ->tag('liip_monitor.additional_reporter', ['alias' => 'symfony_mailer']);
};
