<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Command;

use Liip\Monitor\Check\CheckContext;
use Liip\Monitor\Check\CheckRegistry;
use Liip\Monitor\EventListener\ConsoleCheckListSubscriber;
use Liip\Monitor\EventListener\ConsoleCheckVerboseSubscriber;
use Liip\Monitor\Result\Status;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[AsCommand('monitor:health', 'Runs Health Checks')]
final class HealthCheckCommand extends Command
{
    public function __construct(private CheckRegistry $checkRegistry, private EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Run only the check(s) with this ID', null, $this->ids())
            ->addOption('suite', 's', InputOption::VALUE_REQUIRED, 'Run only the checks in this suite', null, $this->suites())
            ->addOption('no-cache', description: 'Disable caching')
            ->addOption('fail-on-warning', description: 'Fail command if any checks have a warning result')
            ->addOption('fail-on-skip', description: 'Fail command if any checks are skipped')
            ->addOption('fail-on-unknown', description: 'Fail command if any checks have an unknown result')
            ->addOption('json', description: 'Output in JSON format')
        ;
    }

    /**
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isJsonOutput = $input->getOption('json');
        $io = new SymfonyStyle($input, $output);

        if (!$isJsonOutput) {
            $subscriber = $io->isVerbose() ? new ConsoleCheckVerboseSubscriber($io) : new ConsoleCheckListSubscriber($input, $output);
            $this->eventDispatcher->addSubscriber($subscriber);
        }

        $suite = $this->checkRegistry->suite($input->getOption('suite'));

        if (!$suite->count()) {
            throw new \RuntimeException(\sprintf('No checks found for suite "%s"', $suite));
        }

        $results = $suite->run(!$input->getOption('no-cache'));
        $failureStatuses = [];

        if ($input->getOption('fail-on-warning')) {
            $failureStatuses[] = Status::WARNING;
        }

        if ($input->getOption('fail-on-skip')) {
            $failureStatuses[] = Status::SKIP;
        }

        if ($input->getOption('fail-on-unknown')) {
            $failureStatuses[] = Status::UNKNOWN;
        }

        $isFail = $results->defects(...$failureStatuses)->count() > 0;

        if ($isJsonOutput) {
            $output->write(\json_encode($results, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT, 512));

            return $isFail ? self::FAILURE : self::SUCCESS;
        }

        $io->section($input->getOption('suite') ? \sprintf('Running Check Suite "%s"', $suite) : 'Running All Checks');

        if ($input->getOption('no-cache')) {
            $io->note('Running with cache disabled');
        }

        $warnings = $results->warnings();
        $unknowns = $results->unknowns();
        $summary = \array_filter([
            $results->successes()->count() ? \sprintf('%d successful', $results->successes()->count()) : null,
            $warnings->count() ? \sprintf('%d warning%s', $warnings->count(), $warnings->count() > 2 ? 's' : '') : null,
            $results->failures()->count() ? \sprintf('%d failed', $results->failures()->count()) : null,
            $results->errors()->count() ? \sprintf('%d errored', $results->errors()->count()) : null,
            $results->skipped()->count() ? \sprintf('%d skipped', $results->skipped()->count()) : null,
            $unknowns->count() ? \sprintf('%d unknown%s', $unknowns->count(), $unknowns->count() > 2 ? 's' : '') : null,
        ]);
        $message = \sprintf('%d check executed (%s)', $results->count(), \implode(', ', $summary));

        $io->newLine();
        $io->{$isFail ? 'error' : 'success'}($message);

        $this->eventDispatcher->removeSubscriber($subscriber);

        return $isFail ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return string[]
     */
    private function ids(): array
    {
        return \array_keys($this->checkRegistry->suite()->checks());
    }

    /**
     * @return string[]
     */
    private function suites(): array
    {
        return \array_unique(\array_merge(...\array_values(\array_map(
            static fn(CheckContext $check) => $check->suites(),
            $this->checkRegistry->suite()->checks()
        ))));
    }
}
