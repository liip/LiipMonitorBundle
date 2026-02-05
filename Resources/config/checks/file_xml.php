<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('liip_monitor.check.file_xml.label', 'File (XML)');

    $container->services()
        ->set('liip_monitor.check.file_xml', \Laminas\Diagnostics\Check\XmlFile::class)
        ->args(['%%liip_monitor.check.file_xml%%'])
        ->call('setLabel', [param('liip_monitor.check.file_xml.label')])
        ->tag('liip_monitor.check', ['alias' => 'file_xml'])
        ->public();
};
