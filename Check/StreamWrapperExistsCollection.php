<?php

declare(strict_types=1);

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\CheckInterface;

class StreamWrapperExistsCollection implements CheckCollectionInterface
{
    /**
     * @var CheckInterface[]
     */
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $config) {
            $streamWrapperName = $config['name'];

            $check = new StreamWrapperExists($streamWrapperName);

            $label = $config['label'] ?? sprintf('Stream wrapper `%s` exists', $streamWrapperName);
            $check->setLabel($label);

            $this->checks[sprintf('stream_wrapper_exists_%s', $streamWrapperName)] = $check;
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
