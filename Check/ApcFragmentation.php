<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\ApcFragmentation as LaminasApcFragmentation;
use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\ResultInterface;

class ApcFragmentation implements CheckInterface
{
    /**
     * @var CheckInterface
     */
    private $check;

    public function __construct(int $warningThreshold, int $criticalThreshold, string $label = null)
    {
        $check = new LaminasApcFragmentation($warningThreshold, $criticalThreshold);
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
