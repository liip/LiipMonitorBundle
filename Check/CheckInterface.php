<?php

namespace Liip\MonitorBundle\Check;

interface CheckInterface
{
    /**
     * @return \Liip\MonitorBundle\Result\CheckResult
     */
    function check();

    /**
     * @return string
     */
    function getName();
}