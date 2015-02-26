<?php

namespace Liip\MonitorBundle\Tests;

use Liip\MonitorBundle\LiipMonitorBundle;

/**
 * Liip\MonitorBundle\Tests\LiipMonitorBundleTest
 */
class LiipMonitorBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var LiipMonitorBundle
     */
    protected $bundle;

    /**
     * Test bundle build to add all required compiler passes
     */
    public function testBuildWithCompilerPasses()
    {
        $compilerPasses = array(
            'Liip\MonitorBundle\DependencyInjection\Compiler\CheckTagCompilerPass' => true,
            'Liip\MonitorBundle\DependencyInjection\Compiler\CheckCollectionTagCompilerPass' => true,
            'Liip\MonitorBundle\DependencyInjection\Compiler\AdditionalReporterCompilerPass' => true,
        );

        $this->container->expects($this->exactly(count($compilerPasses)))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface'))
            ->willReturnCallback(
                function ($compilerPass) use (&$compilerPasses) {
                    $class = get_class($compilerPass);
                    unset($compilerPasses[$class]);
                }
            );

        $this->bundle->build($this->container);
        $this->assertEmpty($compilerPasses);
    }

    /**
     * Sets up test
     */
    protected function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->bundle = new LiipMonitorBundle();
    }
}
