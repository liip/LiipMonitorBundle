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

use Liip\Monitor\Check\PercentThresholdCheck;
use Liip\Monitor\DependencyInjection\Configuration;
use Liip\Monitor\Result;
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
final class LoadAverageCheck extends PercentThresholdCheck implements \Stringable
{
    private const ONE_MINUTE = 0;
    private const FIVE_MINUTE = 1;
    private const FIFTEEN_MINUTE = 2;
    private const KEYS = [
        '1_minute' => 'oneMinute',
        '5_minute' => 'fiveMinute',
        '15_minute' => 'fifteenMinute',
    ];

    /**
     * @param self::ONE_MINUTE|self::FIVE_MINUTE|self::FIFTEEN_MINUTE $type
     */
    private function __construct(
        private System $system,
        private int $type,
        int|string $warningThreshold,
        int|string $criticalThreshold,
    ) {
        parent::__construct($warningThreshold, $criticalThreshold);
    }

    public function __toString(): string
    {
        return \sprintf('%d Minute System Load Average', match ($this->type) {
            self::ONE_MINUTE => 1,
            self::FIVE_MINUTE => 5,
            self::FIFTEEN_MINUTE => 15,
        });
    }

    public static function configKey(): string
    {
        return 'system_load_average';
    }

    public static function configInfo(): ?string
    {
        return null;
    }

    public static function addConfig(ArrayNodeDefinition $node): NodeDefinition
    {
        foreach (\array_keys(self::KEYS) as $type) {
            $node // @phpstan-ignore-line
                ->info(\sprintf('fails/warns if %s load average is above thresholds', \str_replace('_', '-', $type)))
                ->children()
                    ->arrayNode($type)
                        ->canBeEnabled()
                        ->children()
                            ->scalarNode('warning')
                                ->defaultValue('70%')
                            ->end()
                            ->scalarNode('critical')
                                ->defaultValue('90%')
                            ->end()
                        ->end()
                        ->append(Configuration::addSuiteConfig())
                        ->append(Configuration::addTtlConfig())
                        ->append(Configuration::addLabelConfig())
                        ->append(Configuration::addIdConfig())
                    ->end()
                ->end();
        }

        return $node;
    }

    public static function load(array $config, ContainerBuilder $container): void
    {
        foreach (self::KEYS as $key => $method) {
            $subConfig = $config[$key];

            if (!$subConfig['enabled']) {
                continue;
            }

            $container->register(\sprintf('.liip_monitor.check.%s.%s', self::configKey(), $key), self::class)
                ->setFactory([self::class, $method])
                ->setArguments([
                    new Reference('liip_monitor.info.system'),
                    $subConfig['warning'],
                    $subConfig['critical'],
                ])
                ->addTag('liip_monitor.check', $subConfig)
            ;
        }
    }

    /**
     * @param int<0,100>|string $warningThreshold
     * @param int<0,100>|string $criticalThreshold
     */
    public static function oneMinute(System $system, int|string $warningThreshold, int|string $criticalThreshold): self
    {
        return new self($system, self::ONE_MINUTE, $warningThreshold, $criticalThreshold);
    }

    /**
     * @internal
     *
     * @param int<0,100>|string $warningThreshold
     * @param int<0,100>|string $criticalThreshold
     */
    public static function fiveMinute(System $system, int|string $warningThreshold, int|string $criticalThreshold): self
    {
        return new self($system, self::FIVE_MINUTE, $warningThreshold, $criticalThreshold);
    }

    /**
     * @internal
     *
     * @param int<0,100>|string $warningThreshold
     * @param int<0,100>|string $criticalThreshold
     */
    public static function fifteenMinute(System $system, int|string $warningThreshold, int|string $criticalThreshold): self
    {
        return new self($system, self::FIFTEEN_MINUTE, $warningThreshold, $criticalThreshold);
    }

    public function run(): Result
    {
        $load = $this->system->loadAverages()[$this->type];

        return $this->checkThresholds(
            value: $load,
            summary: $load,
            detail: \sprintf('%d minute system load average is %s', match ($this->type) {
                self::ONE_MINUTE => 1,
                self::FIVE_MINUTE => 5,
                self::FIFTEEN_MINUTE => 15,
            }, $load),
            context: ['load' => $load->decimal()],
        );
    }
}
