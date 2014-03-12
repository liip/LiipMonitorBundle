<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection\Compiler;

use Liip\MonitorBundle\DependencyInjection\Compiler\CheckCollectionTagCompilerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class CheckCollectionTagCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        $runner = new Definition();
        $this->setDefinition('liip_monitor.runner', $runner);

        $check = new Definition();
        $check->addTag('liip_monitor.check_collection');
        $this->setDefinition('example_check_collection', $check);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'liip_monitor.runner',
            'addChecks',
            array(
                new Reference('example_check_collection')
            )
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CheckCollectionTagCompilerPass());
    }
}
