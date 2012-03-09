<?php

namespace Liip\MonitorBundle\Check;

use Liip\MonitorBundle\Result\CheckResult;

abstract class Check implements CheckInterface
{
    public function getName()
    {
        return get_called_class();
    }

    protected function buildResult($message, $status)
    {
        return new CheckResult($this->getName(), $message, $status);
    }
}