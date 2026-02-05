<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.php_flags', \Liip\MonitorBundle\Check\PhpFlagsCollection::class)
        ->args(['%%liip_monitor.check.php_flags%%'])
        ->tag('liip_monitor.check_collection')
        ->public();
};
