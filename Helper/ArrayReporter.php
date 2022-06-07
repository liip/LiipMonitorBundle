<?php

namespace Liip\MonitorBundle\Helper;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Collection as ResultsCollection;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\SkipInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use Laminas\Diagnostics\Result\WarningInterface;
use Laminas\Diagnostics\Runner\Reporter\ReporterInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ArrayReporter implements ReporterInterface
{
    public const STATUS_OK = 'OK';
    public const STATUS_KO = 'KO';

    private $globalStatus = self::STATUS_OK;
    private $results = [];

    public function getResults(): array
    {
        return $this->results;
    }

    public function getGlobalStatus(): string
    {
        return $this->globalStatus;
    }

    /**
     * @return bool|void
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

    /**
     * @return void
     */
    public function onStart(\ArrayObject $checks, $runnerConfig)
    {
        return;
    }

    /**
     * @return bool|void
     */
    public function onBeforeRun(CheckInterface $check, $checkAlias = null)
    {
        return;
    }

    /**
     * @return void
     */
    public function onStop(ResultsCollection $results)
    {
        return;
    }

    /**
     * @return void
     */
    public function onFinish(ResultsCollection $results)
    {
        return;
    }
}
