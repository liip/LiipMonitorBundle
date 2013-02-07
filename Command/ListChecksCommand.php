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
            ->addOption('group', 'g', InputOption::VALUE_OPTIONAL, 'List the checks by group')
            ->setDescription('Lists Health Checks');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException in case there is no or an invalid feed url is given.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $chain = $this->getContainer()->get('liip_monitor.check_chain');
        if (!$input->getOption('group')) {
            foreach ($chain->getGroups() as $group) {

                $output->writeln(sprintf('Group <info>%s</info>', $group));
                foreach ($chain->getChecksByGroup($group) as $service_id) {
                    $output->writeln('  - ' . $service_id);
                }
            }
        } else {
            foreach ($chain->getAvailableChecks() as $service_id) {
                $output->writeln($service_id);
            }
        }
    }
}
