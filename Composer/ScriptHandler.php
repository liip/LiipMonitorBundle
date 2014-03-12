<?php
namespace Liip\MonitorBundle\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as BaseHandler;

/**
 * Composer ScriptHandler can be used to run postInstall/postUpdate health checks
 * when running composer.phar update/install.
 *
 */
class ScriptHandler extends BaseHandler
{
    public static function checkHealth($event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];
        $event->getIO()->write('<info>Performing system health checks...</info>');
        static::executeCommand($event, $appDir, 'monitor:health');
    }
}
