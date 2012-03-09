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

    public function getAvailableChecks()
    {
        return array_keys($this->checks);
    }

    public function getCheckById($id)
    {
        if (!isset($this->checks[$id])) {
            throw new \InvalidArgumentException(sprintf("Check with id: %s doesn't exists", $id));
        }

        return $this->checks[$id];
    }
}