<?php

namespace Liip\MonitorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ListChecksCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    private $defaultGroup;

    /**
     * @param string $defaultGroup
     * @param null $name
     */
    public function __construct($defaultGroup, $name = null)
    {
        $this->defaultGroup = $defaultGroup;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('monitor:list')
            ->setDescription('Lists Health Checks')
            ->addOption('reporters', 'r', InputOption::VALUE_NONE, 'List registered additional reporters')
            ->addOption(
                'group',
                'g',
                InputOption::VALUE_REQUIRED,
                'List checks for given group',
                $this->defaultGroup
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        switch(true){
            case $input->getOption('reporters'):
                $this->listReporters($output);
                break;
            default:
                $this->listChecks($input, $output);
                break;
        }
    }

    protected function listChecks(InputInterface $input, OutputInterface $output)
    {
        $group = $input->getOption('group');
        $runner = $this->getContainer()->get('liip_monitor.runner_' . $group);
        $checks = $runner->getChecks();

        if (0 === count($checks)) {
            $output->writeln('<error>No checks configured.</error>');
        }

        foreach ($runner->getChecks() as $alias => $check) {
            $output->writeln(sprintf('<info>%s</info> %s', $alias, $check->getLabel()));
        }
    }

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
}
