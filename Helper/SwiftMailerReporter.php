<?php

namespace Liip\MonitorBundle\Helper;

use ArrayObject;
use Swift_Mailer;
use Swift_Message;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Collection as ResultsCollection;
use ZendDiagnostics\Result\ResultInterface;
use ZendDiagnostics\Runner\Reporter\ReporterInterface;

/**
 * @author louis <louis@systemli.org>
 */
class SwiftMailerReporter implements ReporterInterface
{
    private $mailer;
    private $recipients;
    private $subject;
    private $sender;
    private $sendOnWarning;

    /**
     * @param Swift_Mailer $mailer
     * @param string|array $recipients
     * @param string       $sender
     * @param string       $subject
     * @param bool         $sendOnWarning
     */
    public function __construct(Swift_Mailer $mailer, $recipients, $sender, $subject, $sendOnWarning = true)
    {
        $this->mailer = $mailer;
        $this->recipients = $recipients;
        $this->sender = $sender;
        $this->subject = $subject;
        $this->sendOnWarning = $sendOnWarning;
    }

    /**
     * {@inheritdoc}
     */
    public function onStart(ArrayObject $checks, $runnerConfig)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onBeforeRun(CheckInterface $check, $checkAlias = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onAfterRun(CheckInterface $check, ResultInterface $result, $checkAlias = null)
    {
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

    private function sendEmail(ResultsCollection $results)
    {
        $body = '';

        foreach ($results as $check) {
            /* @var $check  CheckInterface */
            /* @var $result ResultInterface */
            $result = isset($results[$check]) ? $results[$check] : null;

            if ($result instanceof ResultInterface) {
                $body .= sprintf("Check: %s\n", $check->getLabel());
                $body .= sprintf("Message: %s\n\n", $result->getMessage());
            }
        }

        $message = (new Swift_Message())
            ->setSubject($this->subject)
            ->setFrom($this->sender)
            ->setTo($this->recipients)
            ->setBody($body);

        $this->mailer->send($message);
    }
}
