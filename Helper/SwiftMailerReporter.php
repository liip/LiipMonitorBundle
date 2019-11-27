<?php

namespace Liip\MonitorBundle\Helper;

use ArrayObject;
use Swift_Mailer;
use Swift_Message;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Collection as ResultsCollection;
use ZendDiagnostics\Result\ResultInterface;

/**
 * @author louis <louis@systemli.org>
 */
class SwiftMailerReporter extends AbstractMailReporter
{
    private $mailer;

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

        parent::__construct($recipients, $sender, $subject, $sendOnWarning);
    }

    protected function sendEmail(ResultsCollection $results)
    {
        $body = '';

        foreach ($results as $check) {
            /* @var $check  CheckInterface */
            /* @var $result ResultInterface */
            $result = $results[$check] ?? null;

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
