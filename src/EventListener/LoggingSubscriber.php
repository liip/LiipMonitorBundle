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

use Liip\Monitor\Event\PostRunCheckEvent;
use Liip\Monitor\Event\PreRunCheckEvent;
use Liip\Monitor\Result\Status;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class LoggingSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreRunCheckEvent::class => 'before',
            PostRunCheckEvent::class => 'after',
        ];
    }

    public function before(PreRunCheckEvent $event): void
    {
        $this->logger->info(\sprintf('Running health check "%s"', $event->check()->label()), [
            'check' => $event->check(),
        ]);
    }

    public function after(PostRunCheckEvent $event): void
    {
        $result = $event->result();
        $level = match ($result->status()) {
            Status::WARNING => LogLevel::WARNING,
            Status::FAILURE => LogLevel::ERROR,
            Status::ERROR => LogLevel::CRITICAL,
            Status::SKIP, Status::UNKNOWN => LogLevel::NOTICE,
            default => LogLevel::INFO,
        };

        $this->logger->log($level, \sprintf('Health check "%s": %s', $event->check(), $result), [
            'result' => $result,
        ]);
    }
}
