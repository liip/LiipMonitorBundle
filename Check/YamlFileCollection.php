<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\CheckInterface;

class YamlFileCollection implements CheckCollectionInterface
{
    /**
     * @var CheckInterface[]
     */
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $config) {
            $path = $config['path'];

            $check = new YamlFile($path);

            $label = $config['label'] ?? sprintf('YAML file `%s` exists and valid', $path);
            $check->setLabel($label);

            $this->checks[sprintf('file_yaml_%s', $path)] = $check;
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
