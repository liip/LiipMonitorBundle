<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Check;

use Liip\Monitor\DependencyInjection\Configuration;
use Liip\Monitor\Result;
use Liip\Monitor\Utility\Percent;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
abstract class PercentThresholdCheck implements ConfigurableCheck
{
    protected readonly Percent $warningThreshold;
    protected readonly Percent $criticalThreshold;

    /**
     * @param int<0,100>|string $warningThreshold
     * @param int<0,100>|string $criticalThreshold
     */
    public function __construct(int|string $warningThreshold, int|string $criticalThreshold)
    {
        $this->warningThreshold = Percent::from($warningThreshold)->constrain();
        $this->criticalThreshold = Percent::from($criticalThreshold)->constrain();
    }

    /**
     * @internal
     */
    public static function addConfig(ArrayNodeDefinition $node): NodeDefinition
    {
        return $node // @phpstan-ignore-line
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
        ;
    }

    public static function load(array $config, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container->register(\sprintf('.liip_monitor.check.%s', static::configKey()), static::class)
            ->setArguments([
                $config['warning'],
                $config['critical'],
            ])
            ->addTag('liip_monitor.check', $config)
        ;
    }

    /**
     * @param array<string,mixed> $context
     */
    final protected function checkThresholds(Percent $value, string $summary, ?string $detail = null, array $context = []): Result
    {
        if ($value->isGreaterThanOrEqualTo($this->criticalThreshold)) {
            return Result::failure($summary, $detail, $context);
        }

        if ($value->isGreaterThanOrEqualTo($this->warningThreshold)) {
            return Result::warning($summary, $detail, $context);
        }

        return Result::success($summary, $detail, $context);
    }
}
