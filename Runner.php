<?php

namespace Liip\MonitorBundle;

use ZendDiagnostics\Runner\Reporter\ReporterInterface;
use ZendDiagnostics\Runner\Runner as BaseRunner;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Runner extends BaseRunner
{
    private $additionalReporters = [];

    /**
     * @param string $alias
     */
    public function addAdditionalReporter($alias, ReporterInterface $reporter)
    {
        $this->additionalReporters[$alias] = $reporter;
    }

    public function useAdditionalReporters(array $aliases)
    {
        foreach ($this->additionalReporters as $alias => $reporter) {
            if (in_array($alias, $aliases)) {
                $this->addReporter($reporter);
            }
        }
    }

    /**
     * @return array
     */
    public function getAdditionalReporters()
    {
        return $this->additionalReporters;
    }
}
