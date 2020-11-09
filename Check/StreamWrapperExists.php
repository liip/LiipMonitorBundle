<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Check\StreamWrapperExists as LaminasStreamWrapperExists;
use Laminas\Diagnostics\Result\ResultInterface;

class StreamWrapperExists implements CheckInterface
{
    /**
     * @var CheckInterface
     */
    private $check;

    public function __construct(string $name, string $label = null)
    {
        $check = new LaminasStreamWrapperExists($name);
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
