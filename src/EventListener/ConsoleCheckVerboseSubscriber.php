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
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ConsoleCheckVerboseSubscriber implements EventSubscriberInterface
{
    public function __construct(private SymfonyStyle $io)
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
        $this->io->comment(\sprintf('Running <comment>%s</>...', $event->check()->label()));
    }

    public function after(PostRunCheckEvent $event): void
    {
        $list = [
            \sprintf('<comment>%s</comment>', $event->check()->label()),
            new TableSeparator(),
            ['ID' => $event->check()->id()],
            ['Result' => $event->result()->status()->value],
            ['Summary' => $event->result()->summary()],
            ['Detail' => $event->result()->detail() ?? 'n/a'],
            ['Duration' => Helper::formatTime($event->result()->duration())],
        ];

        if ($context = $event->result()->normalizedContext()) {
            $list[] = new TableSeparator();

            foreach ($context as $key => $value) {
                $list[] = [$key => $value];
            }
        }

        $this->io->definitionList(...$list);
    }
}
