<?php

namespace Liip\MonitorBundle\Check;

use Symfony\Component\DependencyInjection\ContainerAware;

class Runner extends ContainerAware
{
    protected $chain;

    /**
     * @param $chain
     */
    public function __construct(CheckChain $chain)
    {
        $this->chain = $chain;
    }

    /**
     * @param $checkId
     * @return
     */
    public function runCheckById($checkId)
    {
        return $this->runCheck($this->chain->getCheckById($checkId));
    }

    /**
     * @param $checkService
     * @return
     */
    public function runCheck($checkService)
    {
        return $checkService->check();
    }

    public function runAllChecks()
    {
        $results = array();
        foreach ($this->chain->getChecks() as $id => $checkService) {
            $results[$id] = $this->runCheck($checkService);
        }
        return $results;
    }
}