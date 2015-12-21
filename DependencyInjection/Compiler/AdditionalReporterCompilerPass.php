<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AdditionalReporterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasParameter('liip_monitor.runners')) {
            return;
        }

        foreach ($container->getParameter('liip_monitor.runners') as $runnerServiceId) {
            $definition = $container->getDefinition($runnerServiceId);

            foreach ($container->findTaggedServiceIds('liip_monitor.additional_reporter') as $id => $tags) {
                foreach ($tags as $attributes) {
                    $alias = empty($attributes['alias']) ? $id : $attributes['alias'];
                    $definition->addMethodCall('addAdditionalReporter', array($alias, new Reference($id)));
                }
            }
        }
    }
}
