<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Check\SecurityAdvisory as LaminasSecurityAdvisory;
use Laminas\Diagnostics\Result\ResultInterface;

class SecurityAdvisory implements CheckInterface
{
    /**
     * @var CheckInterface
     */
    private $check;

    public function __construct(string $path, string $label = null)
    {
        $check = new LaminasSecurityAdvisory($path);
        $check->setLabel($label);

        $this->check = $check;
    }

    public function check(): ResultInterface
    {
        return $this->check->check();
    }

    public function getLabel(): string
    {
        return $this->check->getLabel();
    }

    public function setLabel(string $label): void
    {
        $this->check->setLabel($label);
    }
}