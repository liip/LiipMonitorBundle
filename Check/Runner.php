<?php

namespace Liip\MonitorBundle\Check;

use Symfony\Component\DependencyInjection\ContainerAware;

class Runner extends ContainerAware
{
    public function runCheckByName($checkName)
    {
        $serviceName = $checkName;

        if (!$this->container->has($serviceName)) {
            throw new \InvalidArgumentException('Wrong value for checkName argument');
        }

        return $this->runCheck($this->container->get($serviceName));
    }

    public function runCheck($checkService)
    {
        $result = $checkService->check();
        return array($checkService->getName(), $result);
    }

    public function runAllChecks()
    {
        $result = array();
        $chain = $this->container->get('monitor.check_chain');
        foreach ($chain->getChecks() as $checkService) {
            $result[] = $this->runCheck($checkService);
        }
        return $result;
    }
}