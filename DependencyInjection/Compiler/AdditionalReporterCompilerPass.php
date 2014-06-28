<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AdditionalReporterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('liip_monitor.runner')) {
            return;
        }

        $definition = $container->getDefinition('liip_monitor.runner');

        foreach ($container->findTaggedServiceIds('liip_monitor.additional_reporter') as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['alias'])) {
                    throw new InvalidArgumentException(sprintf('A name must be set for "%s"', $id));
                }

                $definition->addMethodCall('addAdditionalReporter', array($attributes['alias'], new Reference($id)));
            }
        }
    }
}
