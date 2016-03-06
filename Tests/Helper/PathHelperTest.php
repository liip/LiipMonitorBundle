<?php

namespace Liip\MonitorBundle\Tests\Helper;

use Liip\MonitorBundle\Helper\PathHelper;

class PathHelperTest extends \PHPUnit_Framework_TestCase
{
    private $container = null;

    public function testGetRoutesJs()
    {
        $testedClass = $this->getNewTestedClass();
        $this->assertContains('api.test = "route_test";', $testedClass->getRoutesJs(array('test' => array())));
    }

    public function testGetScriptTags()
    {
        $testedClass = $this->getNewTestedClass();
        $this->assertContains('<script type="text/javascript" charset="utf-8" src="url_path"></script>', $testedClass->getScriptTags(array('path')));
    }

    public function testGetStyleTags()
    {
        $testedClass = $this->getNewTestedClass();
        $this->assertContains('<link rel="stylesheet" href="url_path" type="text/css">', $testedClass->getStyleTags(array('path')));
    }

    private function getContainerSf3Mock()
    {
        $routerMock = $this->getRouterMock();
        $assetHelperMock = $this->getAssetHelperMock();
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($param) use ($routerMock, $assetHelperMock) {
                if ($param == 'router') {
                    return $routerMock;
                } elseif ($param == 'assets.packages') {
                    return $assetHelperMock;
                }
                throw new \Exception();
            }));

        $container
            ->expects($this->any())
            ->method('has')
            ->with($this->equalTo('templating.helper.assets'))
            ->willReturn(false);

        return $container;
    }

    private function getContainerSf2Mock()
    {
        $routerMock = $this->getRouterMock();
        $assetHelperMock = $this->getAssetHelperMock();
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($param) use ($routerMock, $assetHelperMock) {
                if ($param == 'router') {
                    return $routerMock;
                } elseif ($param == 'templating.helper.assets') {
                    return $assetHelperMock;
                }
                throw new \Exception();
            }));

        $container
            ->expects($this->any())
            ->method('has')
            ->with($this->equalTo('templating.helper.assets'))
            ->willReturn(true);

        return $container;
    }

    private function getContainerMock()
    {
        $symfony_version = \Symfony\Component\HttpKernel\Kernel::VERSION;
        if (version_compare($symfony_version, '3.0.0') === -1) {
            $container = $this->getContainerSf2Mock();
        } else {
            $container = $this->getContainerSf3Mock();
        }

        return $container;
    }

    private function getRouterMock()
    {
        $router = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $router
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnCallback(function ($arg) { return 'route_'.$arg; }));

        return $router;
    }

    private function getAssetHelperMock()
    {
        if (class_exists('Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper')) {
            $helper = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper')
                ->disableOriginalConstructor()
                ->getMock();
        } else {
            $helper = $this->getMockBuilder('Symfony\Component\Templating\Helper\AssetsHelper')
                ->disableOriginalConstructor()
                ->getMock();
        }

        $helper
            ->method('getUrl')
            ->will($this->returnCallback(function ($arg) { return 'url_'.$arg; }));

        return $helper;
    }

    public function getNewTestedClass()
    {
        $symfony_version = \Symfony\Component\HttpKernel\Kernel::VERSION;

        return new PathHelper($this->container, $symfony_version);
    }

    public function setUp()
    {
        $this->container = $this->getContainerMock();
    }
}
