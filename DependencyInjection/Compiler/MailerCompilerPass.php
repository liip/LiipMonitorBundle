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
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasParameter('liip_monitor.mailer.enabled')) {
            return;
        }

        if (false === $container->getParameter('liip_monitor.mailer.enabled')) {
            return;
        }

        try {
            $definition = $container->findDefinition('mailer');
        } catch (ServiceNotFoundException $e) {
            throw new \InvalidArgumentException('To enable mail reporting you have to install the "swiftmailer/swiftmailer" or "symfony/mailer".');
        }

        $filename = \Swift_Mailer::class !== $definition->getClass() ? 'symfony_mailer.xml' : 'swift_mailer.xml';

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loader->load($filename);
    }
}
