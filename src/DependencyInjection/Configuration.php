<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\DependencyInjection;

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
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    public const CHECKS = [
        MemoryUsageCheck::class,
        DiskUsageCheck::class,
        FreeDiskSpaceCheck::class,
        RebootRequiredCheck::class,
        LoadAverageCheck::class,
        ApcuMemoryUsageCheck::class,
        ApcuFragmentationCheck::class,
        OpCacheMemoryUsageCheck::class,
        PhpVersionCheck::class,
        ComposerAuditCheck::class,
        SymfonyVersionCheck::class,
        PingUrlCheck::class,
        DbalConnectionCheck::class,
    ];

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('liip_monitor');

        $node = $builder->getRootNode() // @phpstan-ignore-line
            ->children()
                ->integerNode('default_ttl')
                    ->info('Default TTL for checks')
                    ->defaultNull()
                    ->min(0)
                ->end()
                ->arrayNode('logging')
                    ->canBeEnabled()
                ->end()
                ->arrayNode('mailer')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('recipient')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->scalarPrototype()->end()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(fn($v) => [$v])
                            ->end()
                        ->end()
                        ->scalarNode('sender')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('subject')
                            ->defaultValue('Health Check Failed')
                            ->cannotBeEmpty()
                        ->end()
                        ->booleanNode('send_on_warning')->defaultFalse()->end()
                        ->booleanNode('send_on_skip')->defaultFalse()->end()
                        ->booleanNode('send_on_unknown')->defaultFalse()->end()
                    ->end()
                ->end()
                ->arrayNode('checks')
                    ->children()
        ;

        foreach (self::CHECKS as $check) {
            $node->append($this->addCheck($check));
        }

        return $builder;
    }

    public static function addSuiteConfig(): NodeDefinition
    {
        return (new TreeBuilder('suite'))->getRootNode() // @phpstan-ignore-line
            ->beforeNormalization()
                ->ifString()
                ->then(fn($v) => [$v])
            ->end()
            ->prototype('scalar')->cannotBeEmpty()->end()
        ;
    }

    public static function addTtlConfig(): NodeDefinition
    {
        return (new TreeBuilder('ttl', 'integer'))->getRootNode()
            ->defaultNull()
        ;
    }

    public static function addLabelConfig(): NodeDefinition
    {
        return (new TreeBuilder('label', 'scalar'))->getRootNode()
            ->defaultNull()
        ;
    }

    public static function addIdConfig(): NodeDefinition
    {
        return (new TreeBuilder('id', 'scalar'))->getRootNode()
            ->defaultNull()
        ;
    }

    /**
     * @param class-string<ConfigurableCheck> $class
     */
    private function addCheck(string $class): NodeDefinition
    {
        $node = (new TreeBuilder($class::configKey()))->getRootNode();

        if ($info = $class::configInfo()) {
            $node->info($info);
        }

        return $class::addConfig($node); // @phpstan-ignore-line
    }
}
