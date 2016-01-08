<?php

namespace Liip\MonitorBundle\Tests\Helper;

use Liip\MonitorBundle\Helper\RunnerManager;

class RunnerManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var RunnerManager
     */
    private $runnerManager;

    public function groupProvider()
    {
        return array(
            array(null),
            array('default'),
            array('test'),
        );
    }

    /**
     * @dataProvider groupProvider
     *
     * @param string $group
     */
    public function testGetRunner($group)
    {
        $this->container
            ->expects($this->any())
            ->method('getParameter')
            ->with('liip_monitor.default_group')
            ->willReturn('default');

        $this->container
            ->expects($this->any())
            ->method('has')
            ->with('liip_monitor.runner_'.($group ?: 'default'))
            ->willReturn(true);

        $expectedResult = $this->getMock('Liip\MonitorBundle\Runner');

        $this->container
            ->expects($this->any())
            ->method('get')
            ->with('liip_monitor.runner_'.($group ?: 'default'))
            ->willReturn($expectedResult);

        $result = $this->runnerManager->getRunner($group);

        $this->assertSame($expectedResult, $result);
    }

    public function testGetRunnerReturnsNull()
    {
        $this->container
            ->expects($this->any())
            ->method('has')
            ->with('liip_monitor.runner_testgroup')
            ->willReturn(false);

        $result = $this->runnerManager->getRunner('testgroup');

        $this->assertNull($result);
    }

    public function testGetRunners()
    {
        $this->container
            ->expects($this->any())
            ->method('getParameter')
            ->with('liip_monitor.runners')
            ->willReturn(array('liip_monitor.runner_group_1', 'liip_monitor.runner_group_2'));

        $runner1 = $this->getMock('Liip\MonitorBundle\Runner');
        $runner2 = $this->getMock('Liip\MonitorBundle\Runner');
        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                array('liip_monitor.runner_group_1'),
                array('liip_monitor.runner_group_2')
            )
            ->willReturnOnConsecutiveCalls($runner1, $runner2);

        $result = $this->runnerManager->getRunners();

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('group_1', $result);
        $this->assertArrayHasKey('group_2', $result);
        $this->assertSame($runner1, $result['group_1']);
        $this->assertSame($runner2, $result['group_2']);
    }

    public function testGetGroups()
    {
        $this->container
            ->expects($this->any())
            ->method('getParameter')
            ->with('liip_monitor.runners')
            ->willReturn(array('liip_monitor.runner_group_1', 'liip_monitor.runner_group_2'));

        $result = $this->runnerManager->getGroups();

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertContains('group_1', $result);
        $this->assertContains('group_2', $result);
    }

    public function testGetDefaultGroup()
    {
        $expectedResult = 'default';

        $this->container
            ->expects($this->any())
            ->method('getParameter')
            ->with('liip_monitor.default_group')
            ->willReturn($expectedResult);

        $result = $this->runnerManager->getDefaultGroup();

        $this->assertEquals($expectedResult, $result);
    }

    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $this->runnerManager = new RunnerManager($this->container);
    }
}
