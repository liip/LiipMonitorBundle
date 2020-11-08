<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\CheckInterface;

class ClassExistsCollection implements CheckCollectionInterface
{
    /**
     * @var CheckInterface[]
     */
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $config) {
            $className = $config['name'];

            $check = new ClassExists($className);

            $label = $config['label'] ?? sprintf('Class `%s` exists', $className);
            $check->setLabel($label);

            $this->checks[sprintf('class_exists_%s', $className)] = $check;
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
