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
                $config['warning_expression'],
                $config['critical_expression'],
                $config['warning_message'],
                $config['critical_message']
            );
        }
    }

    public function getChecks()
    {
        return $this->checks;
    }
}
