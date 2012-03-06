<?php

namespace Liip\MonitorBundle\Check;

use Liip\MonitorBundle\Result\CheckResult;

abstract class Check implements CheckInterface
{
    public function getName()
    {
        return get_called_class();
    }

    protected function getResult($message, $status)
    {
        return new CheckResult($message, $status);
    }
}