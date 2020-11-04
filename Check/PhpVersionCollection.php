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
        foreach ($configs as $version => $value) {
            $comparisonOperator = $value['operator'];

            $check = new PhpVersion($version, $comparisonOperator);

            $label = $value['label'] ?? sprintf('PHP version "%s" "%s"', $comparisonOperator, $version);
            $check->setLabel($label);

            $this->checks[sprintf('php_version_%s', $version)] = $check;
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
