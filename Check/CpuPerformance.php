<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Check\CpuPerformance as LaminasCpuPerformance;
use Laminas\Diagnostics\Result\ResultInterface;

class CpuPerformance implements CheckInterface
{
    /**
     * @var CheckInterface
     */
    private $check;

    public function __construct(array $config = [])
    {
        $minPerformance = $config['performance'];

        $check = new LaminasCpuPerformance($minPerformance);

        $label = $config['label'] ?? sprintf('Performance is `%s`', $minPerformance);
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
