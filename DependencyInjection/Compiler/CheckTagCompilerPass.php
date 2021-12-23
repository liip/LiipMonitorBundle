<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CheckTagCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('liip_monitor.default_group')) {
            return;
        }

        $defaultGroup = $container->getParameter('liip_monitor.default_group');

        foreach ($container->findTaggedServiceIds('liip_monitor.check') as $id => $tags) {
            foreach ($tags as $attributes) {
                $alias = empty($attributes['alias']) ? $id : $attributes['alias'];
                $group = empty($attributes['group']) ? $defaultGroup : $attributes['group'];

                $runnerDefinition = $container->getDefinition('liip_monitor.runner_'.$group);
                $runnerDefinition->addMethodCall('addCheck', [new Reference($id), $alias]);
            }
        }
    }
}
