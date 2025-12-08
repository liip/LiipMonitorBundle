<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('liip_monitor.check.file_yaml.label', 'File (YAML)');

    $services->set('liip_monitor.check.file_yaml', \Laminas\Diagnostics\Check\YamlFile::class)
        ->public()
        ->args(['%%liip_monitor.check.file_yaml%%'])
        ->call('setLabel', ['%liip_monitor.check.file_yaml.label%'])
        ->tag('liip_monitor.check', ['alias' => 'file_yaml']);
};
