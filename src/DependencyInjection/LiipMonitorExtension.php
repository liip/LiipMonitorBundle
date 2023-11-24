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

use Laminas\Diagnostics\Check\CheckInterface;
use Liip\Monitor\AsCheck;
use Liip\Monitor\Check;
use Liip\Monitor\Check\CheckContext;
use Liip\Monitor\Check\Doctrine\DbalConnectionCheck;
use Liip\Monitor\System\LinuxSystem;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class LiipMonitorExtension extends ConfigurableExtension implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        DbalConnectionCheck::process($container);

        $suites = [];

        foreach ($container->findTaggedServiceIds('liip_monitor.check') as $id => $tags) {
            $definition = $container->getDefinition($id);

            if (!$class = $definition->getClass()) {
                continue;
            }

            if (!\is_a($class, Check::class, true) && !\is_a($class, CheckInterface::class, true)) {
                continue;
            }

            $definition->clearTag('liip_monitor.check');

            $tags = \array_merge(...$tags);
            $suites[] = (array) ($tags['suite'] ?? []);

            $container->register(\sprintf('.%s.context', \ltrim($id, '.')), CheckContext::class)
                ->setArguments([
                    new Reference($id),
                    $tags['ttl'] ?? null,
                    $tags['suite'] ?? [],
                    $tags['label'] ?? null,
                    $tags['id'] ?? null,
                ])
                ->addTag('liip_monitor.check')
            ;
        }

        $suites = \array_filter(\array_unique(\array_merge(...$suites)));

        foreach ($suites as $suite) {
            $suiteDef = (new ChildDefinition('.liip_monitor.check_suite'))
                ->addArgument($suite)
            ;

            $container->setDefinition($id = \sprintf('.liip_monitor.check_suite.%s', $suite), $suiteDef);
            $container->registerAliasForArgument($id, Check\CheckSuite::class, $suite.'Checks');
        }
    }

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void // @phpstan-ignore-line
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');

        if ($mergedConfig['logging']['enabled']) {
            $loader->load('logging.php');
        }

        $container->getDefinition('liip_monitor.check_registry')
            ->setArgument('$defaultTtl', $mergedConfig['default_ttl'])
        ;

        if ('Linux' === \PHP_OS) {
            $container->getDefinition('liip_monitor.info.system')->setClass(LinuxSystem::class);
            $container->setAlias(LinuxSystem::class, 'liip_monitor.info.system');
        }

        $container->registerForAutoconfiguration(Check::class)
            ->addTag('liip_monitor.check')
        ;
        $container->registerForAutoconfiguration(CheckInterface::class)
            ->addTag('liip_monitor.check')
        ;
        $container->registerAttributeForAutoconfiguration(AsCheck::class, static function(ChildDefinition $definition, AsCheck $attribute): void {
            $definition->addTag('liip_monitor.check', \array_filter(\get_object_vars($attribute)));
        });

        foreach (Configuration::CHECKS as $check) {
            if (!isset($mergedConfig['checks'][$check::configKey()])) {
                continue;
            }

            $check::load($mergedConfig['checks'][$check::configKey()], $container);
        }
    }
}
