<?php

namespace Liip\MonitorBundle\Helper;

use Liip\MonitorBundle\Runner;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RunnerManager
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param null|string $group
     *
     * @return null|Runner
     */
    public function getRunner($group)
    {
        $runnerServiceId = $this->getRunnerServiceId($group);

        return $runnerServiceId ? $this->container->get($runnerServiceId) : null;
    }

    /**
     * @return array|Runner[] key/value $group/$runner
     */
    public function getRunners()
    {
        $runnerServiceIds = $this->container->getParameter('liip_monitor.runners');

        $runners = array();

        foreach ($runnerServiceIds as $serviceId) {
            if (preg_match('/liip_monitor.runner_(.+)/', $serviceId, $matches)) {
                $runners[$matches[1]] = $this->container->get($serviceId);
            }
        }

        return $runners;
    }

    /**
     * @return array|string[]
     */
    public function getGroups()
    {
        $runnerServiceIds = $this->container->getParameter('liip_monitor.runners');

        $groups = array();

        foreach ($runnerServiceIds as $serviceId) {
            if (preg_match('/liip_monitor.runner_(.+)/', $serviceId, $matches)) {
                $groups[] = $matches[1];
            }
        }

        return $groups;
    }

    /**
     * @return string
     */
    public function getDefaultGroup()
    {
        return $this->container->getParameter('liip_monitor.default_group');
    }

    /**
     * @param null|string $group
     *
     * @return null|string
     */
    private function getRunnerServiceId($group)
    {
        if (null === $group) {
            $group = $this->getDefaultGroup();
        }

        $runnerServiceId = 'liip_monitor.runner_'.$group;

        return $this->container->has($runnerServiceId) ? $runnerServiceId : null;
    }
}
