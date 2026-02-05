<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.php_version', \Liip\MonitorBundle\Check\PhpVersionCollection::class)
        ->args(['%%liip_monitor.check.php_version%%'])
        ->tag('liip_monitor.check_collection')
        ->public();
};
