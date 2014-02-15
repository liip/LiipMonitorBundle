<?php

namespace Liip\MonitorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ListChecksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('monitor:list')
            ->setDescription('Lists Health Checks');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $runner = $this->getContainer()->get('liip_monitor.runner');
        $checks = $runner->getChecks();

        if (0 === count($checks)) {
            $output->writeln('<error>No checks configured.</error>');
        }

        foreach ($runner->getChecks() as $check) {
            $output->writeln($check->getLabel());
        }
    }
}
