<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class CheckCollectionTagCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('liip_monitor.runner')) {
            return;
        }

        $definition = $container->getDefinition('liip_monitor.runner');

        foreach ($container->findTaggedServiceIds('liip_monitor.check_collection') as $id => $tags) {
            $definition->addMethodCall('addChecks', array(new Reference($id)));
        }
    }
}
