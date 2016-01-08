<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GroupRunnersCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $noRunner = false === $container->hasDefinition('liip_monitor.runner');
        $noDefaultGroup = false === $container->hasParameter('liip_monitor.default_group');

        if ($noRunner || $noDefaultGroup) {
            return;
        }

        $definition = $container->getDefinition('liip_monitor.runner');
        $container->removeDefinition('liip_monitor.runner');

        $defaultGroup = $container->getParameter('liip_monitor.default_group');

        $checkServices = $container->findTaggedServiceIds('liip_monitor.check');
        $checkCollectionServices = $container->findTaggedServiceIds('liip_monitor.check_collection');

        $groups = array_merge(
            array($defaultGroup),
            $this->getGroups($checkServices),
            $this->getGroups($checkCollectionServices),
            $this->getGroupsFromParameter($container)
        );
        $groups = array_unique($groups);

        $runners = array();
        foreach ($groups as $group) {
            $container->setDefinition('liip_monitor.runner_'.$group, clone $definition);
            $runners[] = 'liip_monitor.runner_'.$group;
        }

        $container->setAlias('liip_monitor.runner', 'liip_monitor.runner_'.$defaultGroup);
        $container->setParameter('liip_monitor.runners', $runners);
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
                if (!empty($attributes['group'])) {
                    $groups[$attributes['group']] = true;
                }
            }
        }

        return array_keys($groups);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function getGroupsFromParameter(ContainerBuilder $container)
    {
        $groups = array();

        if ($container->hasParameter('liip_monitor.checks')) {
            $checks = $container->getParameter('liip_monitor.checks');
            foreach (array_keys($checks['groups']) as $group) {
                $groups[] = $group;
            }
        }

        return $groups;
    }
}
