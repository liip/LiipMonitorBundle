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
                    'check1'
                ),
                'app_server' => array(
                    'check1',
                    'check_collection1'
                )
            )
        );
        $this->setParameter('liip_monitor.checks', $checkConfig);

        $check1 = new Definition();
        $check1->addTag('liip_monitor.check', array('alias' => 'check1'));
        $this->setDefinition('liip_monitor.check.check1', $check1);

        $checkCollection1 = new Definition();
        $checkCollection1->addTag('liip_monitor.check_collection');
        $this->setDefinition('liip_monitor.check.check_collection1', $checkCollection1);

        $this->compile();

        $serviceDefinition = $this->container->getDefinition('liip_monitor.check.check1');
        $tags = $serviceDefinition->getTag('liip_monitor.check');

        $this->assertCount(2, $tags);
        $this->assertContains(array('group' => 'default', 'alias' => 'check1'), $tags);
        $this->assertContains(array('group' => 'app_server', 'alias' => 'check1'), $tags);

        $serviceDefinition = $this->container->getDefinition('liip_monitor.check.check_collection1');
        $tags = $serviceDefinition->getTag('liip_monitor.check_collection');

        $this->assertCount(1, $tags);
        $this->assertContains(array('group' => 'app_server', 'alias' => 'check_collection1'), $tags);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function testServiceNotFoundException()
    {
        $checkConfig = array(
            'groups' => array(
                'default' => array(
                    'check1'
                )
            )
        );
        $this->setParameter('liip_monitor.checks', $checkConfig);

        $this->compile();
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\LogicException
     */
    public function testMissingTagInServiceDefinition()
    {
        $checkConfig = array(
            'groups' => array(
                'default' => array(
                    'check1'
                )
            )
        );
        $this->setParameter('liip_monitor.checks', $checkConfig);

        $check1 = new Definition();
        $check1->addTag('liip_monitor.wrong_tag', array('alias' => 'check1'));
        $this->setDefinition('liip_monitor.check.check1', $check1);

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddGroupsCompilerPass());
    }
}
