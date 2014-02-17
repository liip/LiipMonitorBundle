<?php

namespace Liip\MonitorBundle\Helper;

use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\ResultInterface;
use ZendDiagnostics\Result\SkipInterface;
use ZendDiagnostics\Result\SuccessInterface;
use ZendDiagnostics\Result\WarningInterface;
use ZendDiagnostics\Runner\Reporter\ReporterInterface;
use ZendDiagnostics\Result\Collection as ResultsCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ArrayReporter implements ReporterInterface
{
    private $globalStatus = 'OK';
    private $results = array();

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

    /**
     * {@inheritDoc}
     */
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
                $this->globalStatus = 'KO';
                break;

            case $result instanceof SkipInterface:
                $status = 2;
                $statusName = 'check_result_skip';
                break;

            default:
                $status = 3;
                $statusName = 'check_result_critical';
                $this->globalStatus = 'KO';
        }

        $this->results[] = array(
            'checkName' => $check->getLabel(),
            'message' => $result->getMessage(),
            'status' => $status,
            'status_name' => $statusName,
            'service_id' => $checkAlias
        );
    }

    /**
     * {@inheritDoc}
     */
    public function onStart(\ArrayObject $checks, $runnerConfig)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function onBeforeRun(CheckInterface $check, $checkAlias = null)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function onStop(ResultsCollection $results)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function onFinish(ResultsCollection $results)
    {
        return;
    }
}
