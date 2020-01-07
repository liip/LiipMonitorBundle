<?php

namespace Liip\MonitorBundle\Helper;

use ArrayObject;
use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Collection as ResultsCollection;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Runner\Reporter\ReporterInterface;

abstract class AbstractMailReporter implements ReporterInterface
{
    /**
     * @var array|string
     */
    protected $recipients;
    /**
     * @var string
     */
    protected $subject;
    /**
     * @var string
     */
    protected $sender;
    /**
     * @var bool
     */
    protected $sendOnWarning;

    /**
     * @param string|array $recipients
     * @param string       $sender
     * @param string       $subject
     * @param bool         $sendOnWarning
     */
    public function __construct($recipients, $sender, $subject, $sendOnWarning = true)
    {
        $this->recipients = $recipients;
        $this->sender = $sender;
        $this->subject = $subject;
        $this->sendOnWarning = $sendOnWarning;
    }

    public function onStart(ArrayObject $checks, $runnerConfig)
    {
    }

    public function onBeforeRun(CheckInterface $check, $checkAlias = null)
    {
    }

    public function onAfterRun(CheckInterface $check, ResultInterface $result, $checkAlias = null)
    {
    }

    public function onStop(ResultsCollection $results)
    {
    }

    public function onFinish(ResultsCollection $results)
    {
        if ($results->getUnknownCount() > 0) {
            $this->sendEmail($results);

            return;
        }

        if ($results->getWarningCount() > 0 && $this->sendOnWarning) {
            $this->sendEmail($results);

            return;
        }

        if ($results->getFailureCount() > 0) {
            $this->sendEmail($results);

            return;
        }
    }

    abstract protected function sendEmail(ResultsCollection $results);
}
