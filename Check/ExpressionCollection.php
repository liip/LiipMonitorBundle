<?php

namespace Liip\MonitorBundle\Check;

use ZendDiagnostics\Check\CheckCollectionInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ExpressionCollection implements CheckCollectionInterface
{
    private $checks = array();

    public function __construct(array $configs)
    {
        foreach ($configs as $alias => $config) {
            $this->checks[sprintf('expression_%s', $alias)] = new Expression(
                $config['label'],
                $config['warningCheck'],
                $config['criticalCheck'],
                $config['warningMessage'],
                $config['criticalMessage']
            );
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
