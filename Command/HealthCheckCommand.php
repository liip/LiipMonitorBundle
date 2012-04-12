<?php

namespace Liip\MonitorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Liip\MonitorBundle\Result\CheckResult;

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

        $error = false;
        foreach ($results as $result) {
            $msg = sprintf('%s: %s', $result->getCheckName(), $result->getMessage());

            switch ($result->getStatus()) {
                case CheckResult::OK:
                    $output->writeln(sprintf('<info>OK</info> %s', $msg));
                    break;

                case CheckResult::WARNING:
                    $output->writeln(sprintf('<comment>WARNING</comment> %s', $msg));
                    break;

                case CheckResult::CRITICAL:
                    $error = true;
                    $output->writeln(sprintf('<error>CRITICAL %s</error>', $msg));
                    break;

                case CheckResult::UNKNOWN:
                    $output->writeln(sprintf('<error>UNKNOWN<error> %s', $msg));
                    break;
            }
        }

        $output->writeln('done!');

        // return a negative value if an error occurs
        return $error ? 1 : 0;
    }
}
