<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection\Compiler;

use Liip\MonitorBundle\DependencyInjection\Compiler\CheckTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\GroupRunnersCompilerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class CheckTagCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        $defaultGroup = 'gruppo_predefinito';

        $runner = new Definition();
        $this->setDefinition('liip_monitor.runner', $runner);
        $this->setParameter('liip_monitor.default_group', $defaultGroup);

        $check = new Definition();
        $check->addTag('liip_monitor.check');
        $this->setDefinition('example_check', $check);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'liip_monitor.runner_'.$defaultGroup,
            'addCheck',
            array(
                new Reference('example_check'),
                'example_check',
            )
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new GroupRunnersCompilerPass());
        $container->addCompilerPass(new CheckTagCompilerPass());
    }
}
