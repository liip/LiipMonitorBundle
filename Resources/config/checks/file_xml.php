<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('liip_monitor.check.file_xml.label', 'File (XML)');

    $services->set('liip_monitor.check.file_xml', \Laminas\Diagnostics\Check\XmlFile::class)
        ->public()
        ->args(['%%liip_monitor.check.file_xml%%'])
        ->call('setLabel', ['%liip_monitor.check.file_xml.label%'])
        ->tag('liip_monitor.check', ['alias' => 'file_xml']);
};
