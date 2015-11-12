<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class CheckCollectionTagCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasParameter('liip_monitor.default_group')) {
            return;
        }

        $defaultGroup = $container->getParameter('liip_monitor.default_group');

        foreach ($container->findTaggedServiceIds('liip_monitor.check_collection') as $id => $tags) {
            foreach ($tags as $attributes) {
                $group = empty($attributes['group']) ? $defaultGroup : $attributes['group'];

                $runnerDefinition = $container->getDefinition('liip_monitor.runner_'.$group);
                $runnerDefinition->addMethodCall('addChecks', array(new Reference($id)));
            }
        }
    }
}
