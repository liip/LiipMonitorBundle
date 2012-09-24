<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class HealthCheckTagCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('liip_monitor.check_chain')) {
            return;
        }

        $definition = $container->getDefinition('liip_monitor.check_chain');

        foreach ($container->findTaggedServiceIds('liip_monitor.check') as $id => $tags) {
            foreach ($tags as $attributes) {
                $alias = empty($attributes['alias']) ? $id : $attributes['alias'];
                $definition->addMethodCall('addCheck', array($alias, new Reference($id)));
            }
        }
    }
}
