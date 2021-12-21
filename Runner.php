<?php

namespace Liip\MonitorBundle;

use Laminas\Diagnostics\Runner\Reporter\ReporterInterface;
use Laminas\Diagnostics\Runner\Runner as BaseRunner;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Runner extends BaseRunner
{
    private $additionalReporters = [];

    /**
     * @param string $alias
     */
    public function addAdditionalReporter($alias, ReporterInterface $reporter): void
    {
        $this->additionalReporters[$alias] = $reporter;
    }

    public function useAdditionalReporters(array $aliases): void
    {
        foreach ($this->additionalReporters as $alias => $reporter) {
            if (in_array($alias, $aliases)) {
                $this->addReporter($reporter);
            }
        }
    }

    public function getAdditionalReporters(): array
    {
        return $this->additionalReporters;
    }
}
