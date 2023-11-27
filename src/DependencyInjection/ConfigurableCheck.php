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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
interface ConfigurableCheck
{
    public static function configKey(): string;

    public static function configInfo(): ?string;

    public static function addConfig(ArrayNodeDefinition $node): NodeDefinition;

    /**
     * @param mixed[] $config
     */
    public static function load(array $config, ContainerBuilder $container): void;
}
