<?php
namespace Liip\MonitorBundle\Tests\Helper;

use Liip\MonitorBundle\Helper\PathHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Kernel;

class PathHelperTest extends WebTestCase
{
    public function testGenerateRoutes()
    {
        if (Kernel::MAJOR_VERSION === '2' && Kernel::MINOR_VERSION === '7') {
            $environment = 'symfony27';
        } else {
            $environment = 'symfony' . Kernel::MAJOR_VERSION;
        }

        $client = static::createClient(array('environment' => $environment));

        $container = $client->getContainer();

        $pathHelper = new PathHelper($container);

        // test route is defined in Tests/app/routing.yml
        $routes = $pathHelper->generateRoutes(['test_route' => []]);

        $this->assertEquals(['api.test_route = "/monitor";'], $routes);
    }
}
