<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\PhpFlag;

class PhpFlagsCollection implements CheckCollectionInterface
{
    /**
     * @var array
     */
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $setting => $value) {
            $check = new PhpFlag($setting, $value['value']);

            $label = $value['label'] ?? sprintf('PHP flag "%s"', $setting);
            $check->setLabel($label);

            $this->checks[sprintf('php_flag_%s', $setting)] = $check;
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
