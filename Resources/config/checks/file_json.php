<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('liip_monitor.check.file_json.label', 'File (JSON)');

    $container->services()
        ->set('liip_monitor.check.file_json', \Laminas\Diagnostics\Check\JsonFile::class)
        ->args(['%%liip_monitor.check.file_json%%'])
        ->call('setLabel', [param('liip_monitor.check.file_json.label')])
        ->tag('liip_monitor.check', ['alias' => 'file_json'])
        ->public();
};
