<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Check\Symfony;

use Liip\Monitor\Check;
use Liip\Monitor\DependencyInjection\ConfigurableCheck;
use Liip\Monitor\DependencyInjection\Configuration;
use Liip\Monitor\Info\Symfony\SymfonyVersionInfo;
use Liip\Monitor\Result;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class SymfonyVersionCheck implements Check, ConfigurableCheck, \Stringable
{
    public function __construct(private SymfonyVersionInfo $version)
    {
    }

    public function __toString(): string
    {
        return 'Symfony Version';
    }

    public function run(): Result
    {
        if ($this->version->isEol()) {
            return Result::failure(
                \sprintf('%s - the %s branch is EOL', $this->version->currentVersion(), $this->version->branch()),
                context: ['eol_date' => $this->version->supportUntil()]
            );
        }

        if ($this->version->isPatchUpdateRequired()) {
            return Result::warning(
                \sprintf('%s - requires a patch update to %s', $this->version->currentVersion(), $this->version->latestPatchVersion()),
                context: ['latest_patch_version' => $this->version->latestPatchVersion()]
            );
        }

        return Result::success($this->version->currentVersion());
    }

    public static function configKey(): string
    {
        return 'symfony_version';
    }

    public static function configInfo(): ?string
    {
        return 'fails if EOL, warns if patch update required';
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
                new Reference('liip_monitor.info.symfony_version'),
            ])
            ->addTag('liip_monitor.check', $config)
        ;
    }
}
