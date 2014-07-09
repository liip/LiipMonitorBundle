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
    /**
     * @var Swift_Mailer
     */
    private $mailer;
    /**
     * @var string
     */
    private $recipient;
    /**
     * @var string
     */
    private $subject;
    /**
     * @var
     */
    private $sender;

    /**
     * Constructor.
     *
     * @param Swift_Mailer $mailer
     * @param string $recipient
     * @param $sender
     * @param string $subject
     */
    public function __construct(Swift_Mailer $mailer, $recipient, $sender, $subject)
    {
        $this->mailer = $mailer;
        $this->recipient = $recipient;
        $this->sender = $sender;
        $this->subject = $subject;
    }

    /**
     * {@inheritDoc}
     */
    public function onStart(ArrayObject $checks, $runnerConfig)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onBeforeRun(CheckInterface $check, $checkAlias = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onAfterRun(CheckInterface $check, ResultInterface $result, $checkAlias = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onStop(ResultsCollection $results)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onFinish(ResultsCollection $results)
    {
        if ($results->getFailureCount() > 0
            || $results->getWarningCount() > 0
            || $results->getUnknownCount() > 0
        ) {
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

            $message = Swift_Message::newInstance()
                ->setSubject($this->subject)
                ->setFrom($this->sender)
                ->setTo($this->recipient)
                ->setBody($body);

            $this->mailer->send($message);
        }
    }
}
