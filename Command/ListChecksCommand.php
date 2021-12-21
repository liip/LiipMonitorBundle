<?php

namespace Liip\MonitorBundle\Command;

use Liip\MonitorBundle\Helper\RunnerManager;
use Liip\MonitorBundle\Runner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListChecksCommand extends Command
{
    private $runnerManager;
    private $runner;

    public function __construct(RunnerManager $runnerManager, Runner $runner, $name = null)
    {
        $this->runnerManager = $runnerManager;
        $this->runner = $runner;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('monitor:list')
            ->setDescription('Lists Health Checks')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Lists Health Checks of all groups')
            ->addOption('reporters', 'r', InputOption::VALUE_NONE, 'List registered additional reporters')
            ->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'List checks for given group')
            ->addOption('groups', 'G', InputOption::VALUE_NONE, 'List all registered groups')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        switch (true) {
            case $input->getOption('reporters'):
                $this->listReporters($output);
                break;
            case $input->getOption('all'):
                $this->listAllChecks($output);
                break;
            case $input->getOption('groups'):
                $this->listGroups($output);
                break;
            default:
                $this->listChecks($input, $output);
                break;
        }

        return 0;
    }

    protected function listChecks(InputInterface $input, OutputInterface $output): void
    {
        $group = $input->getOption('group');

        $runner = $this->runnerManager->getRunner($group);

        if (null === $runner) {
            $output->writeln('<error>No such group.</error>');

            return;
        }

        $this->doListChecks($output, $runner);
    }

    protected function listAllChecks(OutputInterface $output): void
    {
        foreach ($this->runnerManager->getRunners() as $group => $runner) {
            $output->writeln(sprintf('<fg=yellow;options=bold>%s</>', $group));

            $this->doListChecks($output, $runner);
        }
    }

    protected function listReporters(OutputInterface $output): void
    {
        $reporters = $this->runner->getAdditionalReporters();
        if (0 === count($reporters)) {
            $output->writeln('<error>No additional reporters configured.</error>');
        }

        foreach (array_keys($reporters) as $reporter) {
            $output->writeln($reporter);
        }
    }

    protected function listGroups(OutputInterface $output): void
    {
        foreach ($this->runnerManager->getGroups() as $group) {
            $output->writeln($group);
        }
    }

    private function doListChecks(OutputInterface $output, Runner $runner): void
    {
        $checks = $runner->getChecks();

        if (0 === count($checks)) {
            $output->writeln('<error>No checks configured.</error>');
        }

        foreach ($runner->getChecks() as $alias => $check) {
            $output->writeln(sprintf('<info>%s</info> %s', $alias, $check->getLabel()));
        }
    }
}
