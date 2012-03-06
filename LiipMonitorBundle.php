<?php

namespace Liip\MonitorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Liip\MonitorBundle\DependencyInjection\Compiler\TagCompilerPass;

class LiipMonitorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TagCompilerPass());
    }
}
