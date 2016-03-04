<?php

namespace Liip\MonitorBundle\Tests\Helper;

use Liip\MonitorBundle\Helper\PathHelper;

class PathHelperTest extends \PHPUnit_Framework_TestCase
{

    public function testHelperWithSymfony2()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->any())
            ->method('getParameter')
            ->with('templating.helper.assets')
            ->willReturn('Exist');

        $container
            ->expects($this->any())
            ->method('getParameter')
            ->with('assets.packages')
            ->will($this->throwException(new \Exception()));

        $container
            ->expects($this->any())
            ->method('has')
            ->with('templating.helper.assets')
            ->willReturn(true);

        $this->assertInstanceOf('Liip\MonitorBundle\Helper\PathHelper', new PathHelper($container, '2.0.1'));
    }

    public function testHelperWithSymfony3()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->any())
            ->method('getParameter')
            ->with('assets.packages')
            ->willReturn('Exist');

        $container
            ->expects($this->any())
            ->method('getParameter')
            ->with('templating.helper.assets')
            ->will($this->throwException(new \Exception()));

        $container
            ->expects($this->any())
            ->method('has')
            ->with('templating.helper.assets')
            ->willReturn(false);

        $this->assertInstanceOf('Liip\MonitorBundle\Helper\PathHelper', new PathHelper($container, '3.0.1'));
    }

}
