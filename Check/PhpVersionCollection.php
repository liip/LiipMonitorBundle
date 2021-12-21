<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\PhpVersion;

class PhpVersionCollection implements CheckCollectionInterface
{
    /**
     * @var array
     */
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $version => $comparisonOperator) {
            $check = new PhpVersion($version, $comparisonOperator);
            $check->setLabel(sprintf('PHP version "%s" "%s"', $comparisonOperator, $version));

            $this->checks[sprintf('php_version_%s', $version)] = $check;
        }
    }

    /**
     * @return array|\Traversable
     */
    public function getChecks()
    {
        return $this->checks;
    }
}
