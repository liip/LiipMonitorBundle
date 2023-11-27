<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Check\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ConnectionRegistry;
use Liip\Monitor\Check;
use Liip\Monitor\DependencyInjection\ConfigurableCheck;
use Liip\Monitor\DependencyInjection\Configuration;
use Liip\Monitor\Result;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class DbalConnectionCheck implements Check, ConfigurableCheck, \Stringable
{
    private const ALL_CONNECTIONS = '__ALL__';

    public function __construct(private ConnectionRegistry $connections, private string $name)
    {
    }

    public function __toString(): string
    {
        return \sprintf('DBAL Connection "%s"', $this->name);
    }

    public function run(): Result
    {
        $connection = $this->connections->getConnection($this->name);

        if (!$connection instanceof Connection) {
            return Result::failure(\sprintf('Connection "%s" is not a Doctrine DBAL connection.', $this->name));
        }

        $start = \microtime(true);

        $connection->fetchOne($connection->getDatabasePlatform()->getDummySelectSQL());

        return Result::success(\sprintf('%dms', \round((\microtime(true) - $start) * 1000)));
    }

    public static function configKey(): string
    {
        return 'dbal_connection';
    }

    public static function configInfo(): ?string
    {
        return 'fails if dbal connection fails';
    }

    public static function addConfig(ArrayNodeDefinition $node): NodeDefinition
    {
        return $node // @phpstan-ignore-line
            ->beforeNormalization()
                ->ifTrue(fn($v) => \is_array($v) && \array_is_list($v))
                ->then(fn($v) => \array_map(fn() => [], \array_combine($v, $v)))
            ->end()
            ->beforeNormalization()
                ->ifString()->then(fn(string $v) => [['name' => $v]])
            ->end()
            ->beforeNormalization()
                ->ifTrue()->then(fn() => [['name' => self::ALL_CONNECTIONS]])
            ->end()
            ->beforeNormalization()
                ->ifTrue(fn($v) => \is_array($v) && isset($v['suite']))
                ->then(fn($v) => [['name' => self::ALL_CONNECTIONS, ...$v]])
            ->end()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
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
        if ([self::ALL_CONNECTIONS] === \array_keys($config)) {
            // handle in compiler pass
            $container->setParameter('liip_monitor.check.doctrine_dbal_connection.all', $config[self::ALL_CONNECTIONS]);

            return;
        }

        foreach ($config as $name => $check) {
            $container->register(\sprintf('.liip_monitor.check.doctrine_dbal_connection.%s', $name), self::class)
                ->setArguments([
                    new Reference('doctrine'),
                    $name,
                ])
                ->addTag('liip_monitor.check', $check)
            ;
        }
    }

    public static function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('liip_monitor.check.doctrine_dbal_connection.all')) {
            return;
        }

        $config = $container->getParameter('liip_monitor.check.doctrine_dbal_connection.all');

        $container->getParameterBag()->remove('liip_monitor.check.doctrine_dbal_connection.all');

        if (!$container->hasParameter('doctrine.connections')) {
            throw new LogicException('Could not determine Doctrine DBAL connections. Is doctrine/doctrine-bundle installed/enabled?');
        }

        $config = \array_map(fn() => $config, $container->getParameter('doctrine.connections')); // @phpstan-ignore-line

        self::load($config, $container);
    }
}
