<?php

namespace Liip\MonitorBundle\Helper;

use ArrayObject;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Collection as ResultsCollection;
use ZendDiagnostics\Result\ResultInterface;
use ZendDiagnostics\Runner\Reporter\ReporterInterface;

class StreamedReporter implements ReporterInterface
{
    /**
     * @var ArrayReporter
     */
    private $arrayReporter;

    public function __construct()
    {
        $this->arrayReporter = new ArrayReporter();
    }

    /**
     * {@inheritdoc}
     */
    public function onStart(ArrayObject $checks, $runnerConfig)
    {
        echo '{"checks":[';
    }

    /**
     * {@inheritdoc}
     */
    public function onBeforeRun(CheckInterface $check, $checkAlias = null)
    {
        static $comma = '';
        echo $comma;
        $comma = ',';
    }

    /**
     * {@inheritdoc}
     */
    public function onAfterRun(CheckInterface $check, ResultInterface $result, $checkAlias = null)
    {
        echo json_encode($this->arrayReporter->prepareResult($check, $result, $checkAlias));
        ob_flush();
    }

    /**
     * {@inheritdoc}
     */
    public function onStop(ResultsCollection $results)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onFinish(ResultsCollection $results)
    {
        echo '],"globalStatus":"'.$this->arrayReporter->getGlobalStatus().'"}';
    }
}
