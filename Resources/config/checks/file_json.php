<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('liip_monitor.check.file_json.label', 'File (JSON)');

    $services->set('liip_monitor.check.file_json', \Laminas\Diagnostics\Check\JsonFile::class)
        ->public()
        ->args(['%%liip_monitor.check.file_json%%'])
        ->call('setLabel', ['%liip_monitor.check.file_json.label%'])
        ->tag('liip_monitor.check', ['alias' => 'file_json']);
};
