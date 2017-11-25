<?php

namespace Liip\MonitorBundle;

use Liip\MonitorBundle\DependencyInjection\Compiler\AddGroupsCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\AdditionalReporterCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckCollectionTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\GroupRunnersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LiipMonitorBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        if (method_exists($container, 'registerForAutoconfiguration')) {
            $container->registerForAutoconfiguration('ZendDiagnostics\Check\CheckInterface')
                ->addTag('liip_monitor.check');
        }

        $container->addCompilerPass(new AddGroupsCompilerPass());
        $container->addCompilerPass(new GroupRunnersCompilerPass());
        $container->addCompilerPass(new CheckTagCompilerPass());
        $container->addCompilerPass(new CheckCollectionTagCompilerPass());
        $container->addCompilerPass(new AdditionalReporterCompilerPass());
    }
}
