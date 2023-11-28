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
use Liip\Monitor\Result;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ComposerAuditCheck implements Check, ConfigurableCheck, \Stringable
{
    public function __construct(private string $path, private ?string $composerBinary = null)
    {
    }

    public function __toString(): string
    {
        return 'Composer Security Audit';
    }

    public function run(): Result
    {
        if (!\class_exists(Process::class)) {
            throw new \LogicException('The "symfony/process" component is required to use the ComposerAuditCheck.');
        }

        $binary = $this->composerBinary ?? (new ExecutableFinder())->find('composer', 'composer');
        $process = new Process(
            [
                $binary,
                'audit',
                '--format=json',
                '--locked',
            ],
            $this->path,
        );
        $process->run();

        $advisories = \json_decode($process->getOutput(), true, flags: \JSON_THROW_ON_ERROR)['advisories'] ?? throw new \RuntimeException('Unable to parse Composer audit output.');

        if (!\count($advisories)) {
            return Result::success('No advisories');
        }

        return Result::failure(
            \sprintf('%d advisories', \count($advisories)),
            detail: \implode(', ', \array_keys($advisories)),
            context: ['advisories' => $advisories],
        );
    }

    public static function configKey(): string
    {
        return 'composer_audit';
    }

    public static function configInfo(): ?string
    {
        return 'fails if vulnerabilities found';
    }

    public static function addConfig(ArrayNodeDefinition $node): NodeDefinition
    {
        return $node // @phpstan-ignore-line
            ->canBeEnabled()
            ->children()
                ->scalarNode('path')
                    ->defaultValue('%kernel.project_dir%')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('binary')->defaultNull()->end()
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
                $config['path'],
                $config['binary'],
            ])
            ->addTag('liip_monitor.check', $config)
        ;
    }
}
