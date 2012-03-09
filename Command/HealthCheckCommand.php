<?php

namespace Liip\MonitorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
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
                    'The name of the service to be used to perform the health check.',
                    'all'
                ),
            ));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException in case there is no or an invalid feed url is given.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $checkName = $input->getArgument('checkName');
        $runner = $this->getContainer()->get('monitor.check.runner');

        if ($checkName == 'all') {
            $results = $runner->runAllChecks();
        } else {
            $results = array($runner->runCheckById($checkName));
        }

        foreach ($results as $result) {
            $msg = sprintf('%s: %s', $result->getCheckName(), $result->getMessage());
            if ($result->getStatus()) {
                $output->writeln(sprintf('<info>%s</info>', $msg));
            } else {
                $output->writeln(sprintf('<error>%s</error>', $msg));
            }
        }
    }
}
