<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Liip\Monitor\Check\CheckRegistry;
use Liip\Monitor\Check\CheckSuite;
use Liip\Monitor\Command\HealthCheckCommand;
use Liip\Monitor\Command\ListChecksCommand;
use Liip\Monitor\Info\Php\ApcuCacheInfo;
use Liip\Monitor\Info\Php\OpCacheInfo;
use Liip\Monitor\Info\Php\PhpVersionInfo;
use Liip\Monitor\Info\Symfony\SymfonyVersionInfo;
use Liip\Monitor\Messenger\RunMessageHandler;
use Liip\Monitor\System;
use Liip\Monitor\Info\Php\PhpInfo;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('liip_monitor.check_registry', CheckRegistry::class)
        ->args([
            tagged_iterator('liip_monitor.check'),
            service('cache.app'),
            service('event_dispatcher'),
            abstract_arg('default_ttl'),
        ])
        ->alias(CheckRegistry::class, 'liip_monitor.check_registry')

        ->set('.liip_monitor.messenger.run_check_suite_handler', RunMessageHandler::class)
        ->args([service('liip_monitor.check_registry')])
        ->tag('messenger.message_handler', ['method' => 'runSuite'])
        ->tag('messenger.message_handler', ['method' => 'runCheck'])
        ->tag('messenger.message_handler', ['method' => 'runChecks'])

        ->set('.liip_monitor.check_suite', CheckSuite::class)
        ->abstract()
        ->factory([service('liip_monitor.check_registry'), 'suite'])

        ->set('liip_monitor.check_suite._default')
        ->parent('.liip_monitor.check_suite')
        ->alias(CheckSuite::class, 'liip_monitor.check_suite._default')

        ->set('.liip_monitor.command.list_checks', ListChecksCommand::class)
        ->args([
            service('liip_monitor.check_registry'),
        ])
        ->tag('console.command')

        ->set('.liip_monitor.command.health_check', HealthCheckCommand::class)
        ->args([
            service('liip_monitor.check_registry'),
            service('event_dispatcher')
        ])
        ->tag('console.command')

        ->set('liip_monitor.info.system', System::class)
        ->args([
            service('http_client')->nullOnInvalid(),
        ])
        ->alias(System::class, 'liip_monitor.info.system')

        ->set('liip_monitor.info.php_info', PhpInfo::class)
        ->factory([service('liip_monitor.info.system'), 'php'])
        ->alias(PhpInfo::class, 'liip_monitor.info.php_info')

        ->set('liip_monitor.info.php_apc_cache', ApcuCacheInfo::class)
        ->factory([service('liip_monitor.info.php_info'), 'apcu'])
        ->alias(ApcuCacheInfo::class, 'liip_monitor.info.php_apc_cache')

        ->set('liip_monitor.info.php_op_cache', OpCacheInfo::class)
        ->factory([service('liip_monitor.info.php_info'), 'opcache'])
        ->alias(OpCacheInfo::class, 'liip_monitor.info.php_op_cache')

        ->set('liip_monitor.info.php_version', PhpVersionInfo::class)
        ->factory([service('liip_monitor.info.php_info'), 'version'])
        ->alias(PhpVersionInfo::class, 'liip_monitor.info.php_version')

        ->set('liip_monitor.info.symfony_version', SymfonyVersionInfo::class)
        ->factory([service('liip_monitor.info.php_info'), 'symfonyVersion'])
        ->alias(SymfonyVersionInfo::class, 'liip_monitor.info.symfony_version')
    ;
};
