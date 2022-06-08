<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Liip\MonitorBundle\Runner;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GroupRunnersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
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
            [$defaultGroup],
            $this->getGroups($checkServices),
            $this->getGroups($checkCollectionServices),
            $this->getGroupsFromParameter($container)
        );
        $groups = array_unique($groups);

        $runners = [];
        foreach ($groups as $group) {
            $container->setDefinition('liip_monitor.runner_'.$group, clone $definition);
            $container->registerAliasForArgument('liip_monitor.runner_' . $group, Runner::class, $group . 'Runner');
            $runners[] = 'liip_monitor.runner_'.$group;
        }

        $container->setAlias('liip_monitor.runner', 'liip_monitor.runner_'.$defaultGroup);
        $runner = $container->getAlias('liip_monitor.runner');
        $runner->setPublic(true);

        $container->setParameter('liip_monitor.runners', $runners);
    }

    private function getGroups(array $services): array
    {
        $groups = [];
        foreach ($services as $tags) {
            foreach ($tags as $attributes) {
                if (!empty($attributes['group'])) {
                    $groups[$attributes['group']] = true;
                }
            }
        }

        return array_keys($groups);
    }

    private function getGroupsFromParameter(ContainerBuilder $container): array
    {
        $groups = [];

        if ($container->hasParameter('liip_monitor.checks')) {
            $checks = $container->getParameter('liip_monitor.checks');
            foreach (array_keys($checks['groups']) as $group) {
                $groups[] = $group;
            }
        }

        return $groups;
    }
}
