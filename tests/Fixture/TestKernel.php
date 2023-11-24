<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Fixture;

use ColinODell\PsrTestLogger\TestLogger;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Liip\Monitor\LiipMonitorBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new DoctrineBundle();
        yield new LiipMonitorBundle();
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('framework', [
            'http_method_override' => false,
            'secret' => 'S3CRET',
            'router' => ['utf8' => true],
            'test' => true,
            'messenger' => true,
        ]);

        $c->loadFromExtension('doctrine', [
            'dbal' => [
                'connections' => [
                    'default' => ['url' => 'sqlite:///%kernel.project_dir%/var/data1.db'],
                    'another' => ['url' => 'sqlite:///%kernel.project_dir%/var/data2.db'],
                ],
            ],
        ]);

        $c->loadFromExtension('liip_monitor', [
            'logging' => true,
            'checks' => [
                'system_memory_usage' => true,
                'system_disk_usage' => true,
                'system_free_disk_space' => [
                    'warning' => '20GB',
                    'critical' => '10GB',
                ],
                'system_reboot' => true,
                'system_load_average' => [
                    '1_minute' => true,
                    '5_minute' => true,
                    '15_minute' => true,
                ],
                'apcu_memory_usage' => [
                    'suite' => 'foo',
                ],
                'apcu_fragmentation' => [
                    'suite' => 'foo',
                ],
                'opcache_memory_usage' => true,
                'php_version' => true,
                'composer_audit' => true,
                'symfony_version' => true,
                'dbal_connection' => true,
                'ping_url' => [
                    'Symfony.com' => 'https://symfony.com/',
                ],
            ],
        ]);

        $c->register('logger', TestLogger::class)
            ->addTag('kernel.reset', ['method' => 'reset'])
        ;

        $c->register(CheckService1::class)->setAutowired(true)->setAutoconfigured(true);
        $c->register(CheckService2::class)->setAutowired(true)->setAutoconfigured(true);
        $c->register(CheckService3::class)->setAutowired(true)->setAutoconfigured(true);
        $c->register(CheckService4::class)->setAutowired(true)->setAutoconfigured(true);
        $c->register(CheckService5::class)->setAutowired(true)->setAutoconfigured(true);
        $c->register(CheckService6::class)->setAutowired(true)->setAutoconfigured(true);
        $c->register(CheckService7::class)->setAutowired(true)->setAutoconfigured(true);
        $c->register(TestService::class)->setPublic(true)->setAutowired(true)->setAutoconfigured(true);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
