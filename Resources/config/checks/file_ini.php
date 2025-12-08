<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('liip_monitor.check.file_ini.label', 'File (INI)');

    $services->set('liip_monitor.check.file_ini', \Laminas\Diagnostics\Check\IniFile::class)
        ->public()
        ->args(['%%liip_monitor.check.file_ini%%'])
        ->call('setLabel', ['%liip_monitor.check.file_ini.label%'])
        ->tag('liip_monitor.check', ['alias' => 'file_ini']);
};
