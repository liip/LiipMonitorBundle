<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddGroupsCompilerPass implements CompilerPassInterface
{
    const SERVICE_ID_PREFIX = 'liip_monitor.check.';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('liip_monitor.checks')) {
            return;
        }

        $checkConfig = $container->getParameter('liip_monitor.checks');

        list($checks, $checkCollections) = $this->parseGroups($container, $checkConfig['groups']);

        $this->addGroupTags($container, $checks, 'liip_monitor.check');
        $this->addGroupTags($container, $checkCollections, 'liip_monitor.check_collection');
    }

    private function parseGroups(ContainerBuilder $container, array $data): array
    {
        $checks = [];
        $checkCollections = [];

        foreach ($data as $group => $groupChecks) {
            foreach (array_keys($groupChecks) as $checkName) {
                $serviceId = self::SERVICE_ID_PREFIX.$checkName;
                $checkDefinition = $container->getDefinition($serviceId);

                if ($checkDefinition->hasTag('liip_monitor.check')) {
                    $checks[$checkName][] = $group;
                } elseif ($checkDefinition->hasTag('liip_monitor.check_collection')) {
                    $checkCollections[$checkName][] = $group;
                }
            }
        }

        return [$checks, $checkCollections];
    }

    /**
     * This Method completes the service definitions of each check for a configured group.
     *
     * For every configured check (per group) a parameter has been generated in LiipMonitorExtension::setParameters.
     * So the finally generated parameters have to be injected into each check service definition.
     * (see the preg_match part).
     *
     * @param string $tag
     */
    private function addGroupTags(ContainerBuilder $container, array $checks, $tag): void
    {
        foreach ($checks as $checkName => $groups) {
            $serviceId = self::SERVICE_ID_PREFIX.$checkName;
            $serviceDefinition = $container->getDefinition($serviceId);
            $serviceDefinition->clearTag($tag);

            foreach ($groups as $group) {
                $tmpDefinition = clone $serviceDefinition;
                $tmpDefinition->addTag($tag, ['group' => $group, 'alias' => $checkName]);

                foreach ($tmpDefinition->getArguments() as $argumentIndex => $argument) {
                    if (is_string($argument) && preg_match('/^%%(.*)%%$/', $argument, $matches)) {
                        $newArgument = $container->getParameter($matches[1].'.'.$group);
                        $tmpDefinition->replaceArgument($argumentIndex, $newArgument);
                    }
                }

                $container->setDefinition($serviceId.'.'.$group, $tmpDefinition);
            }

            $container->removeDefinition($serviceId);
        }
    }
}
