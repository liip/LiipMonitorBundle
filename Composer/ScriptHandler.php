<?php
namespace Liip\MonitorBundle\Composer;

use Composer\Script\CommandEvent;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as BaseHandler;

/**
 * Composer ScriptHandler can be used to run postInstall/postUpdate health checks
 * when running composer.phar update/install.
 *
 */
class ScriptHandler extends BaseHandler
{
    public static function checkHealth(CommandEvent $event)
    {
        $options = self::getOptions($event);

        // use Symfony 3.0 dir structure if available
        $consoleDir = isset($options['symfony-bin-dir']) ? $options['symfony-bin-dir'] : $options['symfony-app-dir'];
        $event->getIO()->write('<info>Performing system health checks...</info>');
        static::executeCommand($event, $consoleDir, 'monitor:health');
    }
}
