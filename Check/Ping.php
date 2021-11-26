<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Success;

class Ping implements CheckInterface
{
    private const LABEL = "Simple Ping.";

    public function check()
    {
        return new Success();
    }

    public function getLabel()
    {
        return self::LABEL;
    }
}
