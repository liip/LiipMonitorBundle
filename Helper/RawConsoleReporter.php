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
 * Like ConsoleReporter, but without coloration, and no message.
 */
class RawConsoleReporter implements ReporterInterface
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
                $this->output->write('OK');
                break;

            case $result instanceof WarningInterface:
                $this->output->write('WARNING');
                break;

            case $result instanceof SkipInterface:
                $this->output->write('SKIP');
                break;

            default:
                $this->output->write('FAIL');
        }

        $performanceData = $this->getNagiosPerformanceData();

        $this->output->writeln(sprintf(' %s', $check->getLabel().$performanceData));
    }

    /**
     * @return string
     */
    protected function getNagiosPerformanceData()
    {
        return '';
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
