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

use Liip\Monitor\Check\ConfigurableCheck;
use Liip\Monitor\DependencyInjection\Configuration;
use Liip\Monitor\Result;
use Liip\Monitor\System;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Zenstruck\Bytes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class FreeDiskSpaceCheck implements ConfigurableCheck, \Stringable
{
    private Bytes $warningThreshold;
    private Bytes $criticalThreshold;

    public function __construct(
        private System $system,
        int|string $warningThreshold,
        int|string $criticalThreshold,
        private string $path,
    ) {
        $this->warningThreshold = Bytes::parse($warningThreshold);
        $this->criticalThreshold = Bytes::parse($criticalThreshold);
    }

    public function __toString(): string
    {
        return \sprintf('Free Disk Space (%s)', $this->path);
    }

    public function run(): Result
    {
        $disk = $this->system->disk($this->path);
        $free = $disk->free();
        $summary = $free;
        $context = [
            'free' => $disk->free()->value(),
            'used' => $disk->used()->value(),
            'total' => $disk->total()->value(),
        ];
        $detail = \sprintf('%s free of %s total (%s used)', $free, $disk->total(), $disk->percentUsed());

        if ($free->isLessThanOrEqualTo($this->criticalThreshold)) {
            return Result::failure($summary, $detail, $context);
        }

        if ($free->isLessThanOrEqualTo($this->warningThreshold)) {
            return Result::warning($summary, $detail, $context);
        }

        return Result::success($summary, $detail, $context);
    }

    public static function configKey(): string
    {
        return 'system_free_disk_space';
    }

    public static function configInfo(): string
    {
        return 'fails/warns if disk free space is below thresholds';
    }

    public static function addConfig(ArrayNodeDefinition $node): NodeDefinition
    {
        return $node // @phpstan-ignore-line
            ->beforeNormalization()
                ->ifTrue(fn($v) => \is_array($v) && isset($v['warning']))
                ->then(function($v) {
                    return [
                        [
                            'path' => $v['path'] ?? '/',
                            'warning' => $v['warning'],
                            'critical' => $v['critical'] ?? $v['warning'],
                        ],
                    ];
                })
            ->end()
            ->arrayPrototype()
                ->children()
                    ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('warning')
                        ->isRequired()
                        ->example('20GB')
                    ->end()
                    ->scalarNode('critical')
                        ->isRequired()
                        ->example('5GB')
                    ->end()
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
}
