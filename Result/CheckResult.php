<?php

namespace Liip\MonitorBundle\Result;

class CheckResult
{
    protected $message;
    protected $status;

    public function __construct($message, $status)
    {
        $this->message = $message;
        $this->status = $status;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getStatus()
    {
        return $this->status;
    }
}