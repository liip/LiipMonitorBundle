<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\EventListener;

use Liip\Monitor\Event\PostRunCheckSuiteEvent;
use Liip\Monitor\Result\ResultContext;
use Liip\Monitor\Result\Status;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class MailerSubscriber
{
    /**
     * @param array{
     *     recipient: string[],
     *     sender: ?string,
     *     subject: string,
     *     send_on_warning: bool,
     *     send_on_skip: bool,
     *     send_on_unknown: bool,
     * } $config
     */
    public function __construct(private MailerInterface $mailer, private array $config)
    {
    }

    public function afterSuite(PostRunCheckSuiteEvent $event): void
    {
        $results = $event->results();
        $failureStatuses = [];

        if ($this->config['send_on_warning']) {
            $failureStatuses[] = Status::WARNING;
        }

        if ($this->config['send_on_skip']) {
            $failureStatuses[] = Status::SKIP;
        }

        if ($this->config['send_on_unknown']) {
            $failureStatuses[] = Status::UNKNOWN;
        }

        $defects = $results->defects(...$failureStatuses);

        if (0 === $defects->count()) {
            return;
        }

        $body = \implode(
            "\n",
            \array_map(
                static fn(ResultContext $result) => \sprintf(
                    '[%s] %s',
                    $result->check(),
                    $result,
                ),
                $defects->all(),
            ),
        );

        $message = (new Email())
            ->to(...$this->config['recipient'])
            ->subject($this->config['subject'])
            ->text($body)
        ;

        if ($this->config['sender']) {
            $message->from($this->config['sender']);
        }

        $this->mailer->send($message);
    }
}
