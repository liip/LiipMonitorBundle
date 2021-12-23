<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection\Compiler;

use Liip\MonitorBundle\DependencyInjection\Compiler\AdditionalReporterCompilerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class AdditionalReporterCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcessWithAlias(): void
    {
        $runner = new Definition();
        $this->setDefinition('liip_monitor.runner_default', $runner);
        $this->setParameter('liip_monitor.runners', ['liip_monitor.runner_default']);

        $reporter = new Definition();
        $reporter->addTag('liip_monitor.additional_reporter', ['alias' => 'foo']);
        $this->setDefinition('foo_reporter', $reporter);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'liip_monitor.runner_default',
            'addAdditionalReporter',
            [
                'foo',
                new Reference('foo_reporter'),
            ]
        );
    }

    public function testProcessWithoutAlias(): void
    {
        $runner = new Definition();
        $this->setDefinition('liip_monitor.runner_default', $runner);
        $this->setParameter('liip_monitor.runners', ['liip_monitor.runner_default']);

        $reporter = new Definition();
        $reporter->addTag('liip_monitor.additional_reporter');
        $this->setDefinition('foo_reporter', $reporter);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'liip_monitor.runner_default',
            'addAdditionalReporter',
            [
                'foo_reporter',
                new Reference('foo_reporter'),
            ]
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AdditionalReporterCompilerPass());
    }
}
