<?php

namespace Liip\MonitorBundle\Helper;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\OutputInterface;

use ZendDiagnostics\Runner\Reporter\ReporterInterface;
use ZendDiagnostics\Result\FailureInterface;
use ZendDiagnostics\Result\SuccessInterface;
use ZendDiagnostics\Result\WarningInterface;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\ResultInterface;
use ZendDiagnostics\Runner\Reporter\BasicConsole;
use ZendDiagnostics\Result\Collection as ResultsCollection;

class Reporter implements ReporterInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Console window max width
     *
     * @var int
     */
    protected $width = 80;

    /**
     * Total number of Checks
     *
     * @var int
     */
    protected $total = 0;

    /**
     * Current Runner iteration
     *
     * @var int
     */
    protected $iteration = 1;

    /**
     * Current vertical screen position
     *
     * @var int
     */
    protected $pos = 1;

    /**
     * Width of the string representing total count of Checks
     *
     * @var int
     */
    protected $countLength;

    /**
     * Right-hand side gutter char width
     *
     * @var int
     */
    protected $gutter;

    /**
     * Has the Runner operation been aborted (stopped) ?
     *
     * @var bool
     */
    protected $stopped = false;

    /**
     * Create new BasicConsole reporter.
     *
     * @param int $width Max console window width (defaults to 80 chars)
     */
    public function __construct($width = 80)
    {
        $this->width = (int)$width;
    }

    /**
     * @see \ZendDiagnostics\Runner\Reporter\ReporterInterface
     * @param \ArrayObject $checks
     * @param array       $runnerConfig
     */
    public function onStart(\ArrayObject $checks, $runnerConfig)
    {
        $this->stopped = false;
        $this->iteration = 1;
        $this->pos = 1;
        $this->total = count($checks);

        // Calculate gutter width to accommodate number of tests passed
        if ($this->total <= $this->width) {
            $this->gutter = 0; // everything fits well
        } else {
            $this->countLength = floor(log10($this->total)) + 1;
            $this->gutter = ($this->countLength * 2) + 11;
        }

        $this->consoleWriteLn('Starting diagnostics:');
        $this->consoleWriteLn('');
    }

    /**
     * @see \ZendDiagnostics\Runner\Reporter\ReporterInterface
     * @param CheckInterface $check
     * @return bool|void
     */
    public function onBeforeRun(CheckInterface $check)
    {
    }

    /**
     * @see \ZendDiagnostics\Runner\Reporter\ReporterInterface
     * @param CheckInterface  $check
     * @param ResultInterface $result
     * @return bool|void
     */
    public function onAfterRun(CheckInterface $check, ResultInterface $result)
    {
        // Draw a symbol for each result
        if ($result instanceof SuccessInterface) {
            $this->consoleWrite('<info>.</info>');
        } elseif ($result instanceof FailureInterface) {
            $this->consoleWrite('<error>F</error>');
        } elseif ($result instanceof WarningInterface) {
            $this->consoleWrite('<warning>!</warning>');
        } else {
            $this->consoleWrite('<comment>?</comment>');
        }

        $this->pos++;

        // CheckInterface if we need to move to the next line
        if ($this->gutter > 0 && $this->pos > $this->width - $this->gutter) {
            $this->consoleWrite(
                str_pad(
                    sprintf('%s / %u (%s%%)',
                        str_pad($this->iteration, $this->countLength, ' ', STR_PAD_LEFT),
                        $this->total,
                        str_pad(round($this->iteration / $this->total * 100), 3, ' ', STR_PAD_LEFT)
                    ),
                    $this->gutter,
                    ' ',
                    STR_PAD_LEFT
                )
            );

            $this->pos = 1;
        }

        $this->iteration++;
    }

    /**
     * @see \ZendDiagnostics\Runner\Reporter\ReporterInterface
     * @param ResultsCollection $results
     */
    public function onFinish(ResultsCollection $results)
    {
        $this->consoleWriteLn();
        $this->consoleWriteLn();

        // Display a summary line
        if ($results->getFailureCount() == 0 && $results->getWarningCount() == 0 && $results->getUnknownCount() == 0) {
            $line = 'OK (' . $this->total . ' diagnostic tests)';
            $line = OutputFormatter::escape($line);
            $line = "<info>$line</info>";
        } elseif ($results->getFailureCount() == 0) {
            $line = $results->getWarningCount() . ' warnings, ';
            $line .= $results->getSuccessCount() . ' successful tests';

            if ($results->getUnknownCount() > 0) {
                $line .= ', ' . $results->getUnknownCount() . ' unknown test results';
            }

            $line .= '.';
            $line = OutputFormatter::escape($line);
            $line = "<warning>$line</warning>";
        } else {
            $line = $results->getFailureCount() . ' failures, ';
            $line .= $results->getWarningCount() . ' warnings, ';
            $line .= $results->getSuccessCount() . ' successful tests';

            if ($results->getUnknownCount() > 0) {
                $line .= ', ' . $results->getUnknownCount() . ' unknown test results';
            }

            $line .= '.';

            $line = OutputFormatter::escape($line);
            $line = "<error>$line</error>";
        }

        $this->consoleWrite($line);
        $this->consoleWriteLn();
        $this->consoleWriteLn();
        // Display a list of failures and warnings
        foreach ($results as $key => $check) {
            /* @var $check  \ZendDiagnostics\Check\CheckInterface */
            /* @var $result \ZendDiagnostics\Result\ResultInterface */
            $result = $results[$check];

            if ($result instanceof FailureInterface) {
                $line = OutputFormatter::escape('Failure: ' . $check->getLabel());
                $line = "<error>$line</error>";
                $this->consoleWriteLn($line);
                $message = $result->getMessage();
                if ($message) {
                    $this->consoleWriteLn($message);
                }
                $this->consoleWriteLn();
            } elseif ($result instanceof WarningInterface) {
                $line = OutputFormatter::escape('Warning: ' . $check->getLabel());
                $line = "<warning>$line</warning>";
                $this->consoleWriteLn($line);
                $message = $result->getMessage();
                if ($message) {
                    $this->consoleWriteLn($message);
                }
                $this->consoleWriteLn();
            } elseif (!$result instanceof SuccessInterface) {
                $line = OutputFormatter::escape('Unknown result ' . get_class($result) . ': ' . $check->getLabel());
                $line = "<comment>$line</comment>";
                $this->consoleWriteLn($line);
                $message = $result->getMessage();
                if ($message) {
                    $this->consoleWriteLn($message);
                }
                $this->consoleWriteLn();
            }
        }

        // Display information that the test has been aborted.
        if ($this->stopped) {
            $this->consoleWriteLn('Diagnostics aborted because of a failure.');
        }
    }

    /**
     * @see \ZendDiagnostics\Runner\Reporter\ReporterInterface
     * @param ResultsCollection $results
     */
    public function onStop(ResultsCollection $results)
    {
        $this->stopped = true;
    }

    /**
     * Write text to the console.
     *
     * Feel free to extend this method and add better console handling.
     *
     * @param string $text
     */
    protected function consoleWrite($text)
    {
        $this->output->write($text);
    }

    /**
     * Write text followed by a newline character to the console.
     *
     * Feel free to extend this method and add better console handling.
     *
     * @param string $text
     */
    protected function consoleWriteLn($text = '')
    {
        $this->output->writeln($text);
    }
}
