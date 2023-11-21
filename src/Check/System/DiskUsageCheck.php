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
use Liip\Monitor\DependencyInjection\Configuration;
use Liip\Monitor\Info\StorageInfo;
use Liip\Monitor\System;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class DiskUsageCheck extends StorageUsageCheck implements \Stringable
{
    public function __construct(
        private System $system,
        int|string $warningThreshold,
        int|string $criticalThreshold,
        private string $path,
    ) {
        parent::__construct($warningThreshold, $criticalThreshold);
    }

    public function __toString(): string
    {
        return \sprintf('Disk Usage (%s)', $this->path);
    }

    public static function configKey(): string
    {
        return 'system_disk_usage';
    }

    public static function configInfo(): string
    {
        return 'fails/warns if disk usage % is above thresholds';
    }

    public static function addConfig(ArrayNodeDefinition $node): NodeDefinition
    {
        return $node // @phpstan-ignore-line
            ->beforeNormalization()
                ->ifTrue()->then(fn() => [['path' => '/']])
            ->end()
            ->beforeNormalization()
                ->ifString()->then(fn(string $v) => [['path' => $v]])
            ->end()
            ->beforeNormalization()
                ->ifTrue(fn($v) => \is_array($v) && !\array_is_list($v))
                ->then(function($v) {
                    $v['path'] ??= '/';

                    return [$v];
                })
            ->end()
            ->arrayPrototype()
                ->beforeNormalization()
                    ->ifString()->then(fn(string $v) => ['path' => $v])
                ->end()
                ->children()
                    ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('warning')->defaultValue('70%')->end()
                    ->scalarNode('critical')->defaultValue('90%')->end()
                    ->append(Configuration::addSuiteConfig())
                    ->append(Configuration::addTtlConfig())
                    ->append(Configuration::addLabelConfig())
                    ->append(Configuration::addIdConfig())
                ->end()
            ->end()
        ;
    }

    public static function load(array $config, ContainerBuilder $container): void
    {
        foreach ($config as $check) {
            $container->register(\sprintf('.liip_monitor.check.%s.%s', static::configKey(), \hash('crc32c', $check['path'])), self::class)
                ->setArguments([
                    new Reference('liip_monitor.info.system'),
                    $check['warning'],
                    $check['critical'],
                    $check['path'],
                ])
                ->addTag('liip_monitor.check', $check)
            ;
        }
    }

    protected function detail(StorageInfo $storage): string
    {
        return \sprintf('Disk (%s) is %s used (%s of %s total)', $this->path, $storage->percentUsed(), $storage->used(), $storage->total());
    }

    protected function storage(): StorageInfo
    {
        return $this->system->disk($this->path);
    }
}
