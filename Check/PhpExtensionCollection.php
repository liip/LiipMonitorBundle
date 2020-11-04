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
            $check = new PhpExtension($config['name']);

            if (!empty($config['label'])) {
                $check->setLabel($config['label']);
            }

            $this->checks[sprintf('php_extension_%s', $config['name'])] = $check;
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
