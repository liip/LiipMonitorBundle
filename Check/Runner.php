<?php

namespace Liip\MonitorBundle\Check;

use Symfony\Component\DependencyInjection\ContainerAware;

class Runner extends ContainerAware
{
    protected $chain;

    /**
     * @param \Liip\MonitorBundle\Check\CheckChain $chain
     */
    public function __construct(CheckChain $chain)
    {
        $this->chain = $chain;
    }

    /**
     * @param string $checkId
     * @return \Liip\MonitorBundle\Result\CheckResult
     */
    public function runCheckById($checkId)
    {
        return $this->runCheck($this->chain->getCheckById($checkId));
    }

    /**
     * @param \Liip\MonitorBundle\Check\CheckInterface $checkService
     * @return \Liip\MonitorBundle\Result\CheckResult
     */
    public function runCheck(CheckInterface $checkService)
    {
        return $checkService->check();
    }

    /**
     * @return array
     */
    public function runAllChecks()
    {
        $results = array();
        foreach ($this->chain->getChecks() as $id => $checkService) {
            $results[$id] = $this->runCheck($checkService);
        }

        return $results;
    }
}