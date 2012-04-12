<?php

namespace Liip\MonitorBundle\Result;

class CheckResult
{
    const OK       = 0;
    const WARNING  = 1;
    const CRITICAL = 2;
    const UNKNOWN  = 3;

    protected $checkName;
    protected $message;
    protected $status;

    /**
     * @param string $checkName
     * @param string $message
     * @param integer $status
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
     * @return integer
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
            'checkName'   => $this->checkName,
            'message'     => $this->message,
            'status'      => $this->status,
            'status_name' => $this->getStatusName()
        );
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        $list = self::getStatusList();

        if (!isset($list[$this->getStatus()])) {
            return 'n/a';
        }

        return $list[$this->getStatus()];
    }

    /**
     * @static
     * @return array
     */
    static public function getStatusList()
    {
        return array(
            self::OK       => 'check_result_ok',
            self::WARNING  => 'check_result_warning',
            self::CRITICAL => 'check_result_critical',
            self::UNKNOWN  => 'check_result_unknown',
        );
    }
}