<?php

namespace Liip\MonitorBundle\Helper;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Collection as ResultsCollection;
use ZendDiagnostics\Result\ResultInterface;

class SymfonyMailerReporter extends AbstractMailReporter
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(MailerInterface $mailer, array $recipients, string $sender, string $subject, bool $sendOnWarning = true)
    {
        $this->mailer = $mailer;

        parent::__construct($recipients, $sender, $subject, $sendOnWarning);
    }

    protected function sendEmail(ResultsCollection $results): void
    {
        $body = '';

        foreach ($results as $check) {
            /* @var $check  CheckInterface */
            /* @var $result ResultInterface */
            $result = $results[$check] ?? null;

            if ($result instanceof ResultInterface) {
                $body .= sprintf('[%s] %s', $check->getLabel(), $result->getMessage());
            }
        }

        $message = (new Email())
            ->subject($this->subject)
            ->from($this->sender)
            ->to(...$this->recipients)
            ->text($body);

        $this->mailer->send($message);
    }
}
