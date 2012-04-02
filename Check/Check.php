<?php

namespace Liip\MonitorBundle\Check;

use Liip\MonitorBundle\Result\CheckResult;

abstract class Check implements CheckInterface
{
     /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return get_called_class();
    }

    /**
     * @param string $message
     * @param integer $status
     * @return \Liip\MonitorBundle\Result\CheckResult
     */
    protected function buildResult($message, $status)
    {
        return new CheckResult($this->getName(), $message, $status);
    }
}