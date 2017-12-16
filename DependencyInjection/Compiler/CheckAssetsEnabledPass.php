<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

class CheckAssetsEnabledPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has('liip_monitor.health_controller') && !$container->has('assets.packages')) {
            throw new LogicException('Controller support cannot be enabled unless the frameworkbundle "assets" support is also enabled.');
        }
    }
}
