<?php

namespace Liip\MonitorBundle\Helper;

use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Collection as ResultsCollection;
use ZendDiagnostics\Result\ResultInterface;
use ZendDiagnostics\Result\SkipInterface;
use ZendDiagnostics\Result\SuccessInterface;
use ZendDiagnostics\Result\WarningInterface;
use ZendDiagnostics\Runner\Reporter\ReporterInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ArrayReporter implements ReporterInterface
{
    const STATUS_OK = 'OK';
    const STATUS_KO = 'KO';

    private $globalStatus = self::STATUS_OK;
    private $results = [];

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return string
     */
    public function getGlobalStatus()
    {
        return $this->globalStatus;
    }

    public function onAfterRun(CheckInterface $check, ResultInterface $result, $checkAlias = null)
    {
        switch (true) {
            case $result instanceof SuccessInterface:
                $status = 0;
                $statusName = 'check_result_ok';
                break;

            case $result instanceof WarningInterface:
                $status = 1;
                $statusName = 'check_result_warning';
                $this->globalStatus = self::STATUS_KO;
                break;

            case $result instanceof SkipInterface:
                $status = 2;
                $statusName = 'check_result_skip';
                break;

            default:
                $status = 3;
                $statusName = 'check_result_critical';
                $this->globalStatus = self::STATUS_KO;
        }

        $this->results[] = [
            'checkName' => $check->getLabel(),
            'message' => $result->getMessage(),
            'status' => $status,
            'status_name' => $statusName,
            'service_id' => $checkAlias,
        ];
    }

    public function onStart(\ArrayObject $checks, $runnerConfig)
    {
        return;
    }

    public function onBeforeRun(CheckInterface $check, $checkAlias = null)
    {
        return;
    }

    public function onStop(ResultsCollection $results)
    {
        return;
    }

    public function onFinish(ResultsCollection $results)
    {
        return;
    }
}
