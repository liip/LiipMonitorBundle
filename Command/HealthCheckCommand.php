<?php

namespace Liip\MonitorBundle\Command;

use Liip\MonitorBundle\Helper\ConsoleReporter;
use Liip\MonitorBundle\Helper\RawConsoleReporter;
use Liip\MonitorBundle\Helper\RunnerManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HealthCheckCommand extends Command
{
    private $rawReporter;
    private $reporter;
    private $runnerManager;

    public function __construct(ConsoleReporter $reporter, RawConsoleReporter $rawReporter, RunnerManager $runnerManager, $name = null)
    {
        $this->rawReporter = $rawReporter;
        $this->reporter = $reporter;
        $this->runnerManager = $runnerManager;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('monitor:health')
            ->setDescription('Runs Health Checks')
            ->addArgument(
                'checkName',
                InputArgument::OPTIONAL,
                'The name of the service to be used to perform the health check.'
            )
            ->addOption(
                'reporter',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Additional reporters to run.'
            )
            ->addOption('nagios', null, InputOption::VALUE_NONE, 'Suitable for using as a nagios NRPE command.')
            ->addOption(
                'group',
                'g',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Run Health Checks for given group'
            )
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Run Health Checks of all groups')
        ;
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $failureCount = 0;

        $groups = $input->getOption('group') ?: [null];
        $allGroups = $input->getOption('all');
        $checkName = $input->getArgument('checkName');
        $nagios = $input->getOption('nagios');
        $additionalReporters = $input->getOption('reporter');

        if ($nagios) {
            $reporter = $this->rawReporter;
        } else {
            $reporter = $this->reporter;
        }

        $reporter->setOutput($output);

        if ($allGroups) {
            $groups = $this->runnerManager->getGroups();
        }

        foreach ($groups as $group) {
            if (count($groups) > 1 || $allGroups) {
                $output->writeln(sprintf('<fg=yellow;options=bold>%s</>', $group));
            }

            $runner = $this->runnerManager->getRunner($group);

            if (null === $runner) {
                $output->writeln('<error>No such group.</error>');

                return 1;
            }

            $runner->addReporter($reporter);
            $runner->useAdditionalReporters($additionalReporters);

            if (0 === count($runner->getChecks())) {
                $output->writeln('<error>No checks configured.</error>');
            }

            $results = $runner->run($checkName);

            if ($nagios) {
                if ($results->getUnknownCount()) {
                    return 3;
                }
                if ($results->getFailureCount()) {
                    return 2;
                }
                if ($results->getWarningCount()) {
                    return 1;
                }
            }

            $failureCount += $results->getFailureCount();
        }

        return $failureCount > 0 ? 1 : 0;
    }
}
