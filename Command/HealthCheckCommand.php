<?php

namespace Liip\MonitorBundle\Command;

use Liip\MonitorBundle\Helper\ConsoleReporter;
use Symfony\Component\Console\Input\InputInterface;
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
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $checkName = $input->getArgument('checkName');
        $runner = $this->getContainer()->get('liip_monitor.runner');
        $runner->addReporter(new ConsoleReporter($output));

        if (0 === count($runner->getChecks())) {
            $output->writeln('<error>No checks configured.</error>');
        }

        $runner->run($checkName);
    }
}
