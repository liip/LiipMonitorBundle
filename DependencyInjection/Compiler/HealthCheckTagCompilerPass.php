<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class HealthCheckTagCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('monitor.check_chain')) {
            return;
        }

        $definition = $container->getDefinition('monitor.check_chain');

        foreach ($container->findTaggedServiceIds('monitor.check') as $id => $attributes) {
            $definition->addMethodCall('addCheck', array($id, new Reference($id)));
        }
    }
}
