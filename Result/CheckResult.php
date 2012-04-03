<?php

namespace Liip\MonitorBundle\Result;

class CheckResult
{
    const SUCCESS = true;
    const FAILURE = false;

    protected $checkName;
    protected $message;
    protected $status;

    /**
     * @param string $checkName
     * @param string $message
     * @param boolean $status
     */
    public function __construct($checkName, $message, $status)
    {
        $this->checkName = $checkName;
        $this->message = $message;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getCheckName()
    {
        return $this->checkName;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'checkName' => $this->checkName,
            'message'   => $this->message,
            'status'    => $this->status
        );
    }
}