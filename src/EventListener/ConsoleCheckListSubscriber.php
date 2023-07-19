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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ConsoleCheckListSubscriber implements EventSubscriberInterface
{
    private SymfonyStyle $io;
    private ConsoleSectionOutput $section;

    public function __construct(private InputInterface $input, private OutputInterface $output)
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
        if (!$this->output instanceof ConsoleOutputInterface || !$this->input->isInteractive() || !$this->output->isDecorated()) {
            $this->io = new SymfonyStyle($this->input, $this->output);

            return;
        }

        $this->section = $this->output->section();
        $this->io = new SymfonyStyle($this->input, $this->section);

        $this->io->text(\sprintf('     %s...', $event->check()->label()));
    }

    public function after(PostRunCheckEvent $event): void
    {
        if (isset($this->section)) {
            $this->section->clear();
        }

        $result = $event->result();

        $this->io->text(\sprintf('%s %s: <%s>%s</>',
            match ($result->status()) {
                Status::SUCCESS => '  <info>OK</>',
                Status::WARNING => '<comment>WARN</>',
                Status::FAILURE => '<error>FAIL</>',
                Status::ERROR => ' <error>ERR</>',
                Status::SKIP => '<question>SKIP</>',
                Status::UNKNOWN => ' <question>UNK</>',
            },
            $event->check()->label(),
            match ($result->status()) {
                Status::SUCCESS => 'info',
                Status::WARNING => 'comment',
                Status::FAILURE, Status::ERROR => 'error',
                Status::SKIP, Status::UNKNOWN => 'question',
            },
            $result,
        ));

        unset($this->io, $this->section);
    }
}
