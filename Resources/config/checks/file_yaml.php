<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('liip_monitor.check.file_yaml.label', 'File (YAML)');

    $container->services()
        ->set('liip_monitor.check.file_yaml', \Laminas\Diagnostics\Check\YamlFile::class)
        ->args(['%%liip_monitor.check.file_yaml%%'])
        ->call('setLabel', [param('liip_monitor.check.file_yaml.label')])
        ->tag('liip_monitor.check', ['alias' => 'file_yaml'])
        ->public();
};
