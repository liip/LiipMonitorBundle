<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Check\System;

use Liip\Monitor\Check\StorageUsageCheck;
use Liip\Monitor\Info\StorageInfo;
use Liip\Monitor\System;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class MemoryUsageCheck extends StorageUsageCheck implements \Stringable
{
    public function __construct(private System $system, int|string $warningThreshold, int|string $criticalThreshold)
    {
        parent::__construct($warningThreshold, $criticalThreshold);
    }

    public function __toString(): string
    {
        return 'System Memory';
    }

    public static function configKey(): string
    {
        return 'system_memory_usage';
    }

    public static function configInfo(): string
    {
        return 'fails/warns if system memory usage % is above thresholds';
    }

    public static function load(array $config, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container->register(\sprintf('.liip_monitor.check.%s', static::configKey()), static::class)
            ->setArguments([
                new Reference('liip_monitor.info.system'),
                $config['warning'],
                $config['critical'],
            ])
            ->addTag('liip_monitor.check', $config)
        ;
    }

    protected function detail(StorageInfo $storage): string
    {
        return \sprintf('System memory is %s used (%s of %s total)', $storage->percentUsed(), $storage->used(), $storage->total());
    }

    protected function storage(): StorageInfo
    {
        return $this->system->memory();
    }
}
