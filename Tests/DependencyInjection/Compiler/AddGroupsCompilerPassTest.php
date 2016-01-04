<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection\Compiler;

use Liip\MonitorBundle\DependencyInjection\Compiler\AddGroupsCompilerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddGroupsCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess()
    {
        $checkConfig = array(
            'groups' => array(
                'default' => array(
                    'check1' => array(),
                ),
                'app_server' => array(
                    'check1' => array(),
                    'check_collection1' => array(),
                ),
            ),
        );
        $this->setParameter('liip_monitor.checks', $checkConfig);

        $check1 = new Definition();
        $check1->addTag('liip_monitor.check', array('alias' => 'check1'));
        $this->setDefinition('liip_monitor.check.check1', $check1);

        $checkCollection1 = new Definition();
        $checkCollection1->addTag('liip_monitor.check_collection');
        $this->setDefinition('liip_monitor.check.check_collection1', $checkCollection1);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'liip_monitor.check.check1.default',
            'liip_monitor.check',
            array('group' => 'default', 'alias' => 'check1')
        );

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'liip_monitor.check.check1.app_server',
            'liip_monitor.check',
            array('group' => 'app_server', 'alias' => 'check1')
        );

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'liip_monitor.check.check_collection1.app_server',
            'liip_monitor.check_collection',
            array('group' => 'app_server', 'alias' => 'check_collection1')
        );

        $this->assertContainerBuilderNotHasService('liip_monitor.check.check1');
        $this->assertContainerBuilderNotHasService('liip_monitor.check.check_collection1');
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddGroupsCompilerPass());
    }
}
