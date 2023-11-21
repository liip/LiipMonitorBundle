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
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[AsCommand('monitor:list', 'Lists Health Checks')]
final class ListChecksCommand extends Command
{
    public function __construct(private CheckRegistry $checks)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $suite = $this->checks->suite();

        if (!\count($suite)) {
            $io->error('No checks registered.');

            return self::FAILURE;
        }

        $io->table(
            ['ID', 'Label', 'Suite(s)', 'Cache TTL'],
            \array_map(
                static function(CheckContext $check): array {
                    return [
                        $check->id(),
                        $check->label(),
                        \implode(', ', $check->suites()) ?: 'n/a',
                        match ($check->ttl()) {
                            null => 'n/a',
                            -1 => '(disabled)',
                            default => $check->ttl().'s',
                        },
                    ];
                },
                $suite->checks()
            )
        );

        return self::SUCCESS;
    }
}
