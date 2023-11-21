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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class RebootRequiredCheck implements ConfigurableCheck, \Stringable
{
    public function __construct(private System $system)
    {
    }

    public function __toString(): string
    {
        return 'System Reboot';
    }

    public function run(): Result
    {
        return $this->system->isRebootRequired() ? Result::warning('Required') : Result::success('Not required');
    }

    public static function configKey(): string
    {
        return 'system_reboot';
    }

    public static function configInfo(): string
    {
        return 'warns if system reboot is required';
    }

    public static function addConfig(ArrayNodeDefinition $node): NodeDefinition
    {
        return $node
            ->canBeEnabled()
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
                new Reference('liip_monitor.info.system'),
            ])
            ->addTag('liip_monitor.check', $config)
        ;
    }
}
