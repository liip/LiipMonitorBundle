<?php

namespace Liip\MonitorBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MailerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasParameter('liip_monitor.mailer.enabled')) {
            return;
        }

        if (false === $container->getParameter('liip_monitor.mailer.enabled')) {
            return;
        }

        try {
            $container->findDefinition('mailer');
        } catch (ServiceNotFoundException) {
            throw new \InvalidArgumentException('To enable mail reporting you have to install "symfony/mailer".');
        }

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load('symfony_mailer.php');
    }
}
