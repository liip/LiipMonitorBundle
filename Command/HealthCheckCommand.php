<?php

namespace Liip\MonitorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Liip\MonitorBundle\Helper\Reporter;

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
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $checkName = $input->getArgument('checkName');

        /** @var \ZendDiagnostics\Runner\Runner $runner */
        $runner = $this->getContainer()->get('liip_monitor.check.runner');
        foreach ($runner->getReporters() as $reporter) {
            if ($reporter instanceof Reporter) {
                $reporter->setOutput($output);
            }
        }

        $results = $runner->run($checkName);
        return $results->getFailureCount() ? 1 : 0;
    }
}
