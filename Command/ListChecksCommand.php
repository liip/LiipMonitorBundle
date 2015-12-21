<?php

namespace Liip\MonitorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ListChecksCommand extends ContainerAwareCommand
{
    protected function configure()
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

    protected function execute(InputInterface $input, OutputInterface $output)
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
    }

    protected function listChecks(InputInterface $input, OutputInterface $output)
    {
        $group = $input->getOption('group');

        if (is_null($group)) {
            $group = $this->getContainer()->getParameter('liip_monitor.default_group');
        }

        $runnerServiceId = 'liip_monitor.runner_'.$group;

        if (!$this->getContainer()->has($runnerServiceId)) {
            $output->writeln('<error>No such group.</error>');

            return;
        }

        $this->doListChecks($output, $runnerServiceId);
    }

    /**
     * @param OutputInterface $output
     */
    protected function listAllChecks(OutputInterface $output)
    {
        foreach ($this->getGroups() as $runnerServiceId => $group) {
            $output->writeln(sprintf('<fg=yellow;options=bold>%s</>', $group));

            $this->doListChecks($output, $runnerServiceId);
        }
    }

    /**
     * @param OutputInterface $output
     */
    protected function listReporters(OutputInterface $output)
    {
        $reporters = $this->getContainer()->get('liip_monitor.runner')->getAdditionalReporters();
        if (0 === count($reporters)) {
            $output->writeln('<error>No additional reporters configured.</error>');
        }

        foreach (array_keys($reporters) as $reporter) {
            $output->writeln($reporter);
        }
    }

    /**
     * @param OutputInterface $output
     */
    protected function listGroups(OutputInterface $output)
    {
        foreach ($this->getGroups() as $group) {
            $output->writeln($group);
        }
    }

    /**
     * @param OutputInterface $output
     * @param $runnerServiceId
     */
    private function doListChecks(OutputInterface $output, $runnerServiceId)
    {
        $runner = $this->getContainer()->get($runnerServiceId);
        $checks = $runner->getChecks();

        if (0 === count($checks)) {
            $output->writeln('<error>No checks configured.</error>');
        }

        foreach ($runner->getChecks() as $alias => $check) {
            $output->writeln(sprintf('<info>%s</info> %s', $alias, $check->getLabel()));
        }
    }

    /**
     * @return array key/value $serviceId/$group
     */
    private function getGroups()
    {
        $runnerServiceIds = $this->getContainer()->getParameter('liip_monitor.runners');

        $groups = array();

        foreach ($runnerServiceIds as $serviceId) {
            if (preg_match('/liip_monitor.runner_(.+)/', $serviceId, $matches)) {
                $groups[$serviceId] = $matches[1];
            }
        }

        return $groups;
    }
}
