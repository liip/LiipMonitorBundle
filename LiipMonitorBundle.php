<?php

namespace Liip\MonitorBundle;

use Laminas\Diagnostics\Check\CheckInterface;
use Liip\MonitorBundle\DependencyInjection\Compiler\AddGroupsCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\AdditionalReporterCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckAssetsEnabledPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckCollectionTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\GroupRunnersCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\MailerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LiipMonitorBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        if (method_exists($container, 'registerForAutoconfiguration')) {
            $container->registerForAutoconfiguration(CheckInterface::class)
                ->addTag('liip_monitor.check');
        }

        $container->addCompilerPass(new CheckAssetsEnabledPass());
        $container->addCompilerPass(new AddGroupsCompilerPass());
        $container->addCompilerPass(new GroupRunnersCompilerPass());
        $container->addCompilerPass(new CheckTagCompilerPass());
        $container->addCompilerPass(new CheckCollectionTagCompilerPass());
        $container->addCompilerPass(new AdditionalReporterCompilerPass());
        $container->addCompilerPass(new MailerCompilerPass());
    }
}
