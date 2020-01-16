<?php

namespace Liip\MonitorBundle\Helper;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Collection as ResultsCollection;
use ZendDiagnostics\Result\ResultInterface;
use ZendDiagnostics\Result\SkipInterface;
use ZendDiagnostics\Result\SuccessInterface;
use ZendDiagnostics\Result\WarningInterface;
use ZendDiagnostics\Runner\Reporter\ReporterInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ConsoleReporter implements ReporterInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output = null)
    {
        if (null === $output) {
            $output = new ConsoleOutput();
        }
        $this->output = $output;
    }

    public function onAfterRun(CheckInterface $check, ResultInterface $result, $checkAlias = null)
    {
        switch (true) {
            case $result instanceof SuccessInterface:
                $this->output->write('<info>OK</info>');
                break;

            case $result instanceof WarningInterface:
                $this->output->write('<comment>WARNING</comment>');
                break;

            case $result instanceof SkipInterface:
                $this->output->write('<question>SKIP</question>');
                break;

            default:
                $this->output->write('<error>FAIL</error>');
        }

        $this->output->write(sprintf(' %s', $check->getLabel()));

        if ($message = $result->getMessage()) {
            $this->output->write(sprintf(': %s', $message));
        }

        $this->output->writeln('');
    }

    public function onStart(\ArrayObject $checks, $runnerConfig)
    {
        return;
    }

    public function onBeforeRun(CheckInterface $check, $checkAlias = null)
    {
        return;
    }

    public function onStop(ResultsCollection $results)
    {
        return;
    }

    public function onFinish(ResultsCollection $results)
    {
        return;
    }
}
