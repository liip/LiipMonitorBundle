<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;

class DiskUsageCheck implements CheckInterface
{
    protected $warningThreshold;
    protected $criticalThreshold;
    protected $path;

    public function __construct($warningThreshold, $criticalThreshold, $path)
    {
        $this->warningThreshold = (int) $warningThreshold;
        $this->criticalThreshold = (int) $criticalThreshold;
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function check()
    {
        $df = disk_free_space($this->path);
        $dt = disk_total_space($this->path);
        $du = $dt - $df;
        $dp = ($du / $dt) * 100;

        if ($dp >= $this->criticalThreshold) {
            return new Failure(sprintf('Disc usage too high: %2d percentage.', $dp));
        }

        if ($dp >= $this->warningThreshold) {
            return new Warning(sprintf('Disc usage high: %2d percentage.', $dp));
        }

        return new Success();
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'Disc Usage';
    }
}
