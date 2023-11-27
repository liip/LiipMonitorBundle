<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Check\Php;

use Liip\Monitor\Check;
use Liip\Monitor\DependencyInjection\ConfigurableCheck;
use Liip\Monitor\DependencyInjection\Configuration;
use Liip\Monitor\Info\Php\PhpVersionInfo;
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
final class PhpVersionCheck implements Check, ConfigurableCheck, \Stringable
{
    public function __construct(private PhpVersionInfo $version)
    {
    }

    public function __toString(): string
    {
        return 'PHP Version';
    }

    public function run(): Result
    {
        if ($this->version->isEol()) {
            return Result::failure(
                \sprintf('PHP %s is EOL', $this->version->branch()),
                context: ['eol_date' => $this->version->supportUntil()]
            );
        }

        if ($this->version->isPatchUpdateRequired()) {
            return Result::warning(
                \sprintf('PHP %s requires a patch update to %s', $this->version->currentVersion(), $this->version->latestPatchVersion()),
                context: [
                    'latest_patch_version' => $this->version->latestPatchVersion(),
                    'latest_patch_date' => $this->version->latestPatchReleased(),
                ],
            );
        }

        return Result::success($this->version->currentVersion());
    }

    public static function configKey(): string
    {
        return 'php_version';
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
                new Reference('liip_monitor.info.php_version'),
            ])
            ->addTag('liip_monitor.check', $config)
        ;
    }
}
