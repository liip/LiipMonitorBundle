<?php

namespace Liip\MonitorBundle;

use Liip\MonitorBundle\DependencyInjection\Compiler\AdditionalReporterCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckCollectionTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\GroupRunnersCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckTagCompilerPass;

class LiipMonitorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new GroupRunnersCompilerPass(), PassConfig::TYPE_AFTER_REMOVING);
        $container->addCompilerPass(new CheckTagCompilerPass());
        $container->addCompilerPass(new CheckCollectionTagCompilerPass());
        $container->addCompilerPass(new AdditionalReporterCompilerPass());
    }
}
