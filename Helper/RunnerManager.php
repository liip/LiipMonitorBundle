<?php

namespace Liip\MonitorBundle\Helper;

use Liip\MonitorBundle\Runner;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RunnerManager
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string|null $group
     */
    public function getRunner($group): ?Runner
    {
        $runnerServiceId = $this->getRunnerServiceId($group);

        return $runnerServiceId ? $this->container->get($runnerServiceId) : null;
    }

    /**
     * @return array|Runner[] key/value $group/$runner
     */
    public function getRunners(): array
    {
        $runnerServiceIds = $this->container->getParameter('liip_monitor.runners');

        $runners = [];

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
    public function getGroups(): array
    {
        $runnerServiceIds = $this->container->getParameter('liip_monitor.runners');

        $groups = [];

        foreach ($runnerServiceIds as $serviceId) {
            if (preg_match('/liip_monitor.runner_(.+)/', $serviceId, $matches)) {
                $groups[] = $matches[1];
            }
        }

        return $groups;
    }

    public function getDefaultGroup(): string
    {
        return $this->container->getParameter('liip_monitor.default_group');
    }

    /**
     * @param string|null $group
     */
    private function getRunnerServiceId($group): ?string
    {
        if (null === $group) {
            $group = $this->getDefaultGroup();
        }

        $runnerServiceId = 'liip_monitor.runner_'.$group;

        return $this->container->has($runnerServiceId) ? $runnerServiceId : null;
    }

    public function getReporters(): array
    {
        $runners = $this->getRunners();
        $reporters = [];

        foreach ($runners as $runner) {
            $reporters += array_keys($runner->getReporters() + $runner->getAdditionalReporters());
        }

        return $reporters;
    }
}
