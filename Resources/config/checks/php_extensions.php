<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('liip_monitor.check.php_extensions', \Laminas\Diagnostics\Check\ExtensionLoaded::class)
        ->public()
        ->args(['%%liip_monitor.check.php_extensions%%'])
        ->tag('liip_monitor.check', ['alias' => 'php_extensions']);
};
