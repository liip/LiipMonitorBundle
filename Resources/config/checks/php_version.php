<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.php_version', \Liip\MonitorBundle\Check\PhpVersionCollection::class)
        ->public()
        ->args(['%%liip_monitor.check.php_version%%'])
        ->tag('liip_monitor.check_collection');
};
