<?php

namespace Liip\MonitorBundle\Helper;

use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\ResultInterface;
use ZendDiagnostics\Result\SuccessInterface;
use ZendDiagnostics\Result\WarningInterface;
use ZendDiagnostics\Runner\Reporter\ReporterInterface;
use ZendDiagnostics\Result\Collection as ResultsCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ArrayReporter implements ReporterInterface
{
    protected $globalStatus = 'OK';
    protected $results = array();
    protected $serviceIds = array();

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
    public function onAfterRun(CheckInterface $check, ResultInterface $result)
    {
        switch (true) {
            case $result instanceof SuccessInterface:
                $status = 0;
                $statusName = 'check_result_ok';
                $this->globalStatus = 'KO';
                break;

            case $result instanceof WarningInterface:
                $status = 1;
                $statusName = 'check_result_warning';
                $this->globalStatus = 'KO';
                break;

            default:
                $status = 2;
                $statusName = 'check_result_critical';
        }

        $this->results[] = array(
            'checkName' => $check->getLabel(),
            'message' => $result->getMessage(),
            'status' => $status,
            'status_name' => $statusName,
            'service_id' => $this->serviceIds[spl_object_hash($check)]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function onStart(\ArrayObject $checks, $runnerConfig)
    {
        foreach ($checks as $alias => $check) {
            $this->serviceIds[spl_object_hash($check)] = $alias;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onBeforeRun(CheckInterface $check)
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
