<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\DependencyInjection;

use Liip\Monitor\Check\CheckRegistry;
use Liip\Monitor\Check\ConfigurableCheck;
use Liip\Monitor\Check\Doctrine\DbalConnectionCheck;
use Liip\Monitor\Check\Php\ApcuFragmentationCheck;
use Liip\Monitor\Check\Php\ApcuMemoryUsageCheck;
use Liip\Monitor\Check\Php\ComposerAuditCheck;
use Liip\Monitor\Check\Php\OpCacheMemoryUsageCheck;
use Liip\Monitor\Check\Php\PhpVersionCheck;
use Liip\Monitor\Check\PingUrlCheck;
use Liip\Monitor\Check\Symfony\SymfonyVersionCheck;
use Liip\Monitor\Check\System\DiskUsageCheck;
use Liip\Monitor\Check\System\FreeDiskSpaceCheck;
use Liip\Monitor\Check\System\LoadAverageCheck;
use Liip\Monitor\Check\System\MemoryUsageCheck;
use Liip\Monitor\Check\System\RebootRequiredCheck;
use Liip\Monitor\DependencyInjection\LiipMonitorExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LiipMonitorExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     */
    public function loads_with_no_config(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService('liip_monitor.check_registry', CheckRegistry::class);
    }

    /**
     * @test
     * @dataProvider checksThatCanBeEnabled
     *
     * @param class-string<ConfigurableCheck> $check
     */
    public function simple_enable_checks(string $check): void
    {
        $this->loadCheck([$check::configKey() => true]);

        $this->assertContainerBuilderHasService('.liip_monitor.check.'.$check::configKey(), $check);
    }

    public static function checksThatCanBeEnabled(): iterable
    {
        yield [MemoryUsageCheck::class];
        yield [RebootRequiredCheck::class];
        yield [ApcuMemoryUsageCheck::class];
        yield [ApcuFragmentationCheck::class];
        yield [OpCacheMemoryUsageCheck::class];
        yield [PhpVersionCheck::class];
        yield [ComposerAuditCheck::class];
        yield [SymfonyVersionCheck::class];
    }

    /**
     * @test
     */
    public function enable_disk_usage(): void
    {
        $this->loadCheck(['system_disk_usage' => true]);

        $this->assertContainerBuilderHasService('.liip_monitor.check.system_disk_usage.2cd3e1ab', DiskUsageCheck::class);
    }

    /**
     * @test
     */
    public function enable_free_disk_space(): void
    {
        $this->loadCheck(['system_free_disk_space' => [
            'warning' => '20gb',
            'critical' => '10gb',
        ]]);

        $this->assertContainerBuilderHasService('.liip_monitor.check.system_free_disk_space.2cd3e1ab', FreeDiskSpaceCheck::class);
    }

    /**
     * @test
     */
    public function enable_dbal_collection(): void
    {
        $this->loadCheck(['dbal_connection' => true]);
        $this->assertContainerBuilderHasParameter('liip_monitor.check.doctrine_dbal_connection.all', [
            'suite' => [],
            'ttl' => null,
            'label' => null,
            'id' => null,
        ]);

        $this->loadCheck(['dbal_connection' => ['suite' => 'foo']]);
        $this->assertContainerBuilderHasParameter('liip_monitor.check.doctrine_dbal_connection.all', [
            'suite' => ['foo'],
            'ttl' => null,
            'label' => null,
            'id' => null,
        ]);

        $this->loadCheck(['dbal_connection' => 'default']);
        $this->assertContainerBuilderHasService('.liip_monitor.check.doctrine_dbal_connection.default', DbalConnectionCheck::class);

        $this->loadCheck(['dbal_connection' => ['first', 'second']]);
        $this->assertContainerBuilderHasService('.liip_monitor.check.doctrine_dbal_connection.first', DbalConnectionCheck::class);
        $this->assertContainerBuilderHasService('.liip_monitor.check.doctrine_dbal_connection.second', DbalConnectionCheck::class);
    }

    /**
     * @test
     */
    public function enable_ping_url_check(): void
    {
        $this->loadCheck(['ping_url' => [
            'https://example.com/',
            [
                'url' => 'https://symfony.com',
            ],
            'foo' => 'https://liip.ch',
        ]]);

        $this->assertContainerBuilderHasService('.liip_monitor.check.ping_url.0', PingUrlCheck::class);
        $this->assertContainerBuilderHasService('.liip_monitor.check.ping_url.1', PingUrlCheck::class);
        $this->assertContainerBuilderHasService('.liip_monitor.check.ping_url.foo', PingUrlCheck::class);
    }

    /**
     * @test
     */
    public function load_average(): void
    {
        $this->loadCheck(['system_load_average' => [
            '1_minute' => true,
            '5_minute' => true,
            '15_minute' => true,
        ]]);

        $this->assertContainerBuilderHasService('.liip_monitor.check.system_load_average.1_minute', LoadAverageCheck::class);
        $this->assertContainerBuilderHasService('.liip_monitor.check.system_load_average.5_minute', LoadAverageCheck::class);
        $this->assertContainerBuilderHasService('.liip_monitor.check.system_load_average.15_minute', LoadAverageCheck::class);
    }

    protected function getContainerExtensions(): array
    {
        return [new LiipMonitorExtension()];
    }

    private function loadCheck(array $config): void
    {
        $this->load(['checks' => $config]);
    }
}
