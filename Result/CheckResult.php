<?php

namespace Liip\MonitorBundle\Result;

class CheckResult
{
    protected $checkName;
    protected $message;
    protected $status;

    public function __construct($checkName, $message, $status)
    {
        $this->checkName = $checkName;
        $this->message = $message;
        $this->status = $status;
    }

    public function getCheckName()
    {
        return $this->checkName;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function toArray()
    {
        return array(
            'checkName' => $this->checkName,
            'message' => $this->message,
            'status' => $this->status
        );
    }
}