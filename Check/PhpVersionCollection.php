<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckCollectionInterface;
use ZendDiagnostics\Check\PhpVersion;

class PhpVersionCollection implements CheckCollectionInterface
{
    /**
     * @var array
     */
    private $checks = array();

    /**
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        foreach ($configs as $version => $comparisonOperator) {
            $check = new PhpVersion($version, $comparisonOperator);
            $check->setLabel(sprintf('PHP version "%s" "%s"', $comparisonOperator, $version));

            $this->checks[sprintf('php_version_%s', $version)] = $check;
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
