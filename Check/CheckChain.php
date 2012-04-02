<?php

namespace Liip\MonitorBundle\Check;

use Liip\MonitorBundle\Check\CheckInterface;

final class CheckChain
{
    protected $checks = array();

    /**
     * @param array $checks
     */
    public function __construct(array $checks = array())
    {
        foreach ($checks as $serviceId => $check) {
            $this->addCheck($serviceId, $check);
        }
    }

    /**
     * @param string $serviceId
     * @param CheckInterface $check
     * @return void
     */
    public function addCheck($serviceId, CheckInterface $check)
    {
        $this->checks[$serviceId] = $check;
    }

    /**
     * @return array
     */
    public function getChecks()
    {
        return $this->checks;
    }

    /**
     * @return array
     */
    public function getAvailableChecks()
    {
        return array_keys($this->checks);
    }

    /**
     * @throws \InvalidArgumentException
     * @param string $id
     * @return \Liip\MonitorBundle\Check\CheckInterface
     */
    public function getCheckById($id)
    {
        if (!isset($this->checks[$id])) {
            throw new \InvalidArgumentException(sprintf("Check with id: %s doesn't exists", $id));
        }

        return $this->checks[$id];
    }
}