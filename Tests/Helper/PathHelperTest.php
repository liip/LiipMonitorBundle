<?php

namespace Liip\MonitorBundle\Tests\Helper;

use Liip\MonitorBundle\Helper\PathHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Kernel;

class PathHelperTest extends WebTestCase
{
    static public function getKernelClass()
    {
        require_once __DIR__.'/../app/AppKernel.php';

        return 'AppKernel';
    }

    public function testGenerateRoutes()
    {
        $environment = 'symfony' . Kernel::MAJOR_VERSION;
        $client = static::createClient(array('environment' => $environment));

        $container = $client->getKernel()->getContainer();

        $pathHelper = $container->get('liip_monitor.helper');

        // test route is defined in Tests/app/routing.yml
        $routes = $pathHelper->generateRoutes(['test_route' => []]);

        $this->assertEquals(['api.test_route = "/monitor";'], $routes);
    }
}
