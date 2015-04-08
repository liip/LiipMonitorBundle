<?php

namespace Liip\MonitorBundle\Command;

use Liip\MonitorBundle\Helper\ConsoleReporter;
use Liip\MonitorBundle\Helper\RawConsoleReporter;
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
                )
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $checkName = $input->getArgument('checkName');
        $runner = $this->getContainer()->get('liip_monitor.runner');

        if ($input->getOption('nagios')) {
            $runner->addReporter(new RawConsoleReporter($output));
        } else {
            $runner->addReporter(new ConsoleReporter($output));
        }
        $runner->useAdditionalReporters($input->getOption('reporter'));

        if (0 === count($runner->getChecks())) {
            $output->writeln('<error>No checks configured.</error>');
        }

        /** @var \ZendDiagnostics\Result\Collection $results */
        $results = $runner->run($checkName);
        if ($input->getOption('nagios')) {
            if ($results->getUnknownCount()) {
                $returnCode = 3;
            } else if($results->getFailureCount()) {
                $returnCode = 2;
            } else if($results->getWarningCount()) {
                $returnCode = 1;
            } else {
                $returnCode = 0; // We may have som skipped although
            }
        } else {
            $returnCode = $results->getFailureCount() ? 1 : 0;
        }

        return $returnCode;
    }
}
