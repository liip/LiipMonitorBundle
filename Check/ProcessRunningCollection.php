<?php

namespace Liip\MonitorBundle\Check;

use Laminas\Diagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\ProcessRunning;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ProcessRunningCollection implements CheckCollectionInterface
{
    private $checks = [];

    public function __construct(array $configs)
    {
        foreach ($configs as $config) {
            $processName = $config['name'];

            $check = new ProcessRunning($processName);

            $label = $config['label'] ?? sprintf('Process "%s" running', $processName);
            $check->setLabel($label);

            $this->checks[sprintf('process_%s_running', $processName)] = $check;
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
