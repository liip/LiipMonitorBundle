<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddGroupsCompilerPass implements CompilerPassInterface
{
    const SERVICE_ID_PREFIX = 'liip_monitor.check.';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('liip_monitor.checks')) {
            return;
        }

        $checkConfig = $container->getParameter('liip_monitor.checks');

        list($checks, $checkCollections) = $this->parseGroups($container, $checkConfig['groups']);

        $this->addGroupTags($container, $checks, 'liip_monitor.check');
        $this->addGroupTags($container, $checkCollections, 'liip_monitor.check_collection');
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $data
     *
     * @return array
     */
    private function parseGroups(ContainerBuilder $container, array $data)
    {
        $checks = array();
        $checkCollections = array();

        foreach ($data as $group => $groupChecks) {
            foreach ($groupChecks as $checkName) {
                $serviceId = self::SERVICE_ID_PREFIX . $checkName;
                $checkDefinition = $container->getDefinition($serviceId);

                if ($checkDefinition->hasTag('liip_monitor.check')) {
                    $checks[$checkName][] = $group;
                } elseif ($checkDefinition->hasTag('liip_monitor.check_collection')) {
                    $checkCollections[$checkName][] = $group;
                }

            }
        }

        return array($checks, $checkCollections);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $checks
     * @param string           $tag
     */
    private function addGroupTags(ContainerBuilder $container, array $checks, $tag) {
        foreach ($checks as $checkName => $groups) {
            $serviceId = self::SERVICE_ID_PREFIX . $checkName;
            $serviceDefinition = $container->getDefinition($serviceId);

            $serviceDefinition->clearTag($tag);

            foreach ($groups as $group) {
                $serviceDefinition->addTag($tag, array('group' => $group, 'alias' => $checkName));
            }
        }
    }
}
