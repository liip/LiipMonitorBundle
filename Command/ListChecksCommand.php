<?php

namespace Liip\MonitorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
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
        /** @var \ZendDiagnostics\Runner\Runner $runner */
        $runner = $this->getContainer()->get('liip_monitor.check.runner');
        foreach ($runner->getChecks() as $key => $check) {
            if (is_string($key)) {
                $output->write($key.': ');
            }
            $output->writeln($check->getName());
        }
    }
}
