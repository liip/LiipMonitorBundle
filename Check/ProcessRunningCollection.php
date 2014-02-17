<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckCollectionInterface;
use ZendDiagnostics\Check\ProcessRunning;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ProcessRunningCollection implements CheckCollectionInterface
{
    private $checks = array();

    public function __construct($processes)
    {
        if (!is_array($processes)) {
            $processes = array($processes);
        }

        foreach ($processes as $process) {
            $check = new ProcessRunning($process);
            $check->setLabel(sprintf('Process "%s" running', $process));

            $this->checks[sprintf('process_%s_running', $process)] = $check;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getChecks()
    {
        return $this->checks;
    }
}
