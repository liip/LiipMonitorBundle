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
            $check = new PhpFlag($setting, $value);
            $check->setLabel(sprintf('PHP flag "%s"', $setting));

            $this->checks[sprintf('php_flag_%s', $setting)] = $check;
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
