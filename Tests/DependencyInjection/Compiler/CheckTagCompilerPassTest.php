<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection\Compiler;

use Liip\MonitorBundle\DependencyInjection\Compiler\CheckTagCompilerPass;
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
        $runner = new Definition();
        $this->setDefinition('liip_monitor.runner', $runner);

        $check = new Definition();
        $check->addTag('liip_monitor.check');
        $this->setDefinition('example_check', $check);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'liip_monitor.runner',
            'addCheck',
            array(
                new Reference('example_check'),
                'example_check'
            )
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CheckTagCompilerPass());
    }
}
