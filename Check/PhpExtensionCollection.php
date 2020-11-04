<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\CheckInterface;

class PhpExtensionCollection implements CheckCollectionInterface
{
    /**
     * @var CheckInterface[]
     */
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $config) {
            $extensionName = $config['name'];

            $check = new PhpExtension($extensionName);

            $label = $config['label'] ?? sprintf('PHP extension `%s` is loaded', $extensionName);
            $check->setLabel($label);

            $this->checks[sprintf('php_extension_%s', $extensionName)] = $check;
        }
    }

    /**
     * @return CheckInterface[]
     */
    public function getChecks(): array
    {
        return $this->checks;
    }
}
