<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('liip_monitor.check.file_ini.label', 'File (INI)');

    $container->services()
        ->set('liip_monitor.check.file_ini', \Laminas\Diagnostics\Check\IniFile::class)
        ->args(['%%liip_monitor.check.file_ini%%'])
        ->call('setLabel', [param('liip_monitor.check.file_ini.label')])
        ->tag('liip_monitor.check', ['alias' => 'file_ini'])
        ->public();
};
