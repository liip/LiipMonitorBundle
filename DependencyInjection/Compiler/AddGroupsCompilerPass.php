<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class AddGroupsCompilerPass implements CompilerPassInterface
{
    const TAG_CHECK = 'liip_monitor.check';
    const TAG_COLLECTION = 'liip_monitor.check_collection';
    const SERVICE_ID_PREFIX = 'liip_monitor.check.';

    /** @var array  */
    private $serviceDefinitions = array();

    /** @var array  */
    private $tagNames = array();

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasParameter('liip_monitor.checks')) {
            $checkConfig = $container->getParameter('liip_monitor.checks');

            foreach ($checkConfig['groups'] as $group => $checks) {
                foreach ($checks as $check) {
                    $serviceId = self::SERVICE_ID_PREFIX . $check;

                    $checkDefinition = $this->getServiceDefinition($container, $serviceId);
                    $tagName = $this->getTagName($serviceId);
                    $checkDefinition->addTag($tagName, array('group' => $group, 'alias' => $check));
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string $serviceId
     *
     * @return Definition
     */
    private function getServiceDefinition(ContainerBuilder $container, $serviceId)
    {
        if (!isset($this->serviceDefinitions[$serviceId])) {
            if (!$container->hasDefinition($serviceId)) {
                throw new ServiceNotFoundException($serviceId);
            }

            $serviceDefinition = $container->getDefinition($serviceId);

            $this->detectTagName($serviceDefinition, $serviceId);
            $serviceDefinition->clearTag($this->getTagName($serviceId));

            $this->serviceDefinitions[$serviceId] = $serviceDefinition;
        }

        return $this->serviceDefinitions[$serviceId];
    }

    /**
     * @param Definition $checkDefinition
     * @param string $serviceId
     */
    private function detectTagName(Definition $checkDefinition, $serviceId)
    {
        $tagName = null;

        foreach ($checkDefinition->getTags() as $name => $tags) {
            if (in_array($name, array(self::TAG_CHECK, self::TAG_COLLECTION))) {
                $tagName = $name;
                break;
            }
        }

        if (is_null($tagName)) {
            throw new LogicException(
                sprintf('missing tag in service definition (%s, %s)', self::TAG_CHECK, self::TAG_COLLECTION)
            );
        }

        $this->tagNames[$serviceId] = $tagName;
    }

    /**
     * @param string $serviceId
     * @return string
     */
    private function getTagName($serviceId)
    {
        return $this->tagNames[$serviceId];
    }
}
