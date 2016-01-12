<?php

namespace Liip\MonitorBundle\Command;

use Liip\MonitorBundle\Helper\RunnerManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class HealthCheckCommand extends ContainerAwareCommand
{
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $failureCount = 0;

        $groups = $input->getOption('group') ?: array(null);
        $allGroups = $input->getOption('all');
        $checkName = $input->getArgument('checkName');
        $nagios = $input->getOption('nagios');
        $additionalReporters = $input->getOption('reporter');

        if ($nagios) {
            $reporter = $this->getContainer()->get('liip_monitor.helper.raw_console_reporter');
        } else {
            $reporter = $this->getContainer()->get('liip_monitor.helper.console_reporter');
        }

        /** @var RunnerManager $runnerManager */
        $runnerManager = $this->getContainer()->get('liip_monitor.helper.runner_manager');

        if ($allGroups) {
            $groups = $runnerManager->getGroups();
        }

        foreach ($groups as $group) {
            if (count($groups) > 1 || $allGroups) {
                $output->writeln(sprintf('<fg=yellow;options=bold>%s</>', $group));
            }

            $runner = $runnerManager->getRunner($group);

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
                } elseif ($results->getFailureCount()) {
                    return 2;
                } elseif ($results->getWarningCount()) {
                    return 1;
                }
            }

            $failureCount += $results->getFailureCount();
        }

        return $failureCount > 0 ? 1 : 0;
    }
}
