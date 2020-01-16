<?php

namespace Liip\MonitorBundle\Helper;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Collection as ResultsCollection;
use Laminas\Diagnostics\Result\ResultInterface;
use Swift_Mailer;
use Swift_Message;

/**
 * @author louis <louis@systemli.org>
 */
class SwiftMailerReporter extends AbstractMailReporter
{
    private $mailer;

    /**
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
