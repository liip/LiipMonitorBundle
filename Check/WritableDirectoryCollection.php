<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\CheckInterface;

class WritableDirectoryCollection implements CheckCollectionInterface
{
    /**
     * @var CheckInterface[]
     */
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $config) {
            $directoryName = $config['path'];

            $check = new WritableDirectory($directoryName);

            $label = $config['label'] ?? sprintf('Directory `%s` is writable', $directoryName);
            $check->setLabel($label);

            $this->checks[sprintf('writable_directory_%s', $directoryName)] = $check;
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