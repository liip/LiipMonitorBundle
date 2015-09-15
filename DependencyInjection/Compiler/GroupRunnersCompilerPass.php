<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GroupRunnersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('liip_monitor.runner')) {
            return;
        }

        $definition = $container->getDefinition('liip_monitor.runner');
        $container->removeDefinition('liip_monitor.runner');

        $defaultGroup = $container->getParameter('liip_monitor.default_group');

        $checkServices           = $container->findTaggedServiceIds('liip_monitor.check');
        $checkCollectionServices = $container->findTaggedServiceIds('liip_monitor.check_collection');

        $groups = array($defaultGroup);
        $groups = array_merge($groups, $this->getGroups($checkServices));
        $groups = array_merge($groups, $this->getGroups($checkCollectionServices));
        $groups = array_unique($groups);

        foreach ($groups as $group) {
            $container->setDefinition('liip_monitor.runner_' . $group, $definition);
        }

        $container->setAlias('liip_monitor.runner', 'liip_monitor.runner_' . $defaultGroup);
    }

    /**
     * @param array $services
     *
     * @return array
     */
    private function getGroups(array $services)
    {
        $groups = array();
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                $group = isset($attributes['group']) ? $attributes['group'] : 'default';
                $groups[$group] = true;
            }
        }

        return array_keys($groups);
    }
}
