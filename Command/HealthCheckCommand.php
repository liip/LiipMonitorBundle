<?php

namespace Liip\MonitorBundle\Command;

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
            ->setDefinition(array(
                new InputArgument(
                    'checkName',
                    InputArgument::OPTIONAL,
                    'The name of the service to be used to perform the health check.'
                ),
                new InputOption(
                    'reporter',
                    null,
                    InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                    'Additional reporters to run.',
                    array()
                ),
                new InputOption(
                    'nagios',
                    null,
                    InputOption::VALUE_NONE,
                    'Suitable for using as a nagios NRPE command.'
                ),
                new InputOption('group', 'g', InputOption::VALUE_REQUIRED, 'List checks for given group'),
            ));
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $checkName = $input->getArgument('checkName');
        $group = $input->getOption('group');

        if (is_null($group)) {
            $group = $this->getContainer()->getParameter('liip_monitor.default_group');
        }

        $runnerServiceId = 'liip_monitor.runner_'.$group;

        if (!$this->getContainer()->has($runnerServiceId)) {
            $output->writeln('<error>No such group.</error>');

            return 1;
        }

        $runner = $this->getContainer()->get('liip_monitor.runner_'.$group);

        if ($input->getOption('nagios')) {
            $reporter = $this->getContainer()->get('liip_monitor.helper.raw_console_reporter');
        } else {
            $reporter = $this->getContainer()->get('liip_monitor.helper.console_reporter');
        }

        $runner->addReporter($reporter);
        $runner->useAdditionalReporters($input->getOption('reporter'));

        if (0 === count($runner->getChecks())) {
            $output->writeln('<error>No checks configured.</error>');
        }

        /** @var \ZendDiagnostics\Result\Collection $results */
        $results = $runner->run($checkName);
        if ($input->getOption('nagios')) {
            if ($results->getUnknownCount()) {
                return 3;
            } elseif ($results->getFailureCount()) {
                return 2;
            } elseif ($results->getWarningCount()) {
                return 1;
            }
        }

        return $results->getFailureCount() ? 1 : 0;
    }
}
