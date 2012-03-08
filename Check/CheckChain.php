<?php

namespace Liip\MonitorBundle\Check;

use Liip\MonitorBundle\Check\CheckInterface;

class CheckChain
{
    protected $checks;

    public function __construct()
    {
        $this->checks = array();
    }

    public function addCheck($service_id, CheckInterface $check)
    {
        $this->checks[$service_id] = $check;
    }

    public function getChecks()
    {
        return $this->checks;
    }
}