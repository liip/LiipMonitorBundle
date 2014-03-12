<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckCollectionInterface;
use ZendDiagnostics\Check\PhpFlag;

class PhpFlagsCollection implements CheckCollectionInterface
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
        foreach ($configs as $setting => $value) {
            $check = new PhpFlag($setting, $value);
            $check->setLabel(sprintf('PHP flag "%s"', $setting));

            $this->checks[sprintf('php_flag_%s', $setting)] = $check;
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
