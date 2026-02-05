<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check.php_extensions', \Laminas\Diagnostics\Check\ExtensionLoaded::class)
        ->args(['%%liip_monitor.check.php_extensions%%'])
        ->tag('liip_monitor.check', ['alias' => 'php_extensions'])
        ->public();
};
