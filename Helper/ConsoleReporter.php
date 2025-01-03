<?php

namespace Liip\MonitorBundle\Helper;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Collection as ResultsCollection;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\SkipInterface;
use Laminas\Diagnostics\Result\SuccessInterface;
use Laminas\Diagnostics\Result\WarningInterface;
use Laminas\Diagnostics\Runner\Reporter\ReporterInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ConsoleReporter implements ReporterInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct(?OutputInterface $output = null)
    {
        if (null === $output) {
            $output = new ConsoleOutput();
        }
        $this->output = $output;
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @return bool|void
     */
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

    /**
     * @return void
     */
    public function onStart(\ArrayObject $checks, $runnerConfig)
    {
        return;
    }

    /**
     * @return bool|void
     */
    public function onBeforeRun(CheckInterface $check, $checkAlias = null)
    {
        return;
    }

    /**
     * @return void
     */
    public function onStop(ResultsCollection $results)
    {
        return;
    }

    /**
     * @return void
     */
    public function onFinish(ResultsCollection $results)
    {
        return;
    }
}
