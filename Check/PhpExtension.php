<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Check\ExtensionLoaded;
use Laminas\Diagnostics\Result\ResultInterface;

class PhpExtension implements CheckInterface
{
    /**
     * @var CheckInterface
     */
    private $check;

    public function __construct(string $extensionName, string $label = null)
    {
        $check = new ExtensionLoaded($extensionName);
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
