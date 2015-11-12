<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection\Compiler;

use Liip\MonitorBundle\DependencyInjection\Compiler\CheckCollectionTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\GroupRunnersCompilerPass;
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
        $defaultGroup = 'grupo_predeterminado';

        $runner = new Definition();
        $this->setDefinition('liip_monitor.runner', $runner);
        $this->setParameter('liip_monitor.default_group', $defaultGroup);

        $check = new Definition();
        $check->addTag('liip_monitor.check_collection');
        $this->setDefinition('example_check_collection', $check);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'liip_monitor.runner_'.$defaultGroup,
            'addChecks',
            array(
                new Reference('example_check_collection'),
            )
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new GroupRunnersCompilerPass());
        $container->addCompilerPass(new CheckCollectionTagCompilerPass());
    }
}
