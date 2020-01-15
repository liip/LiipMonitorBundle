<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection\Compiler;

use Liip\MonitorBundle\DependencyInjection\Compiler\MailerCompilerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Mailer\MailerInterface;

class MailerCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcessWithDisableMailer()
    {
        $this->setParameter('liip_monitor.mailer.enabled', false);

        $this->compile();

        $this->assertContainerBuilderNotHasService('liip_monitor.reporter.symfony_mailer');
        $this->assertContainerBuilderNotHasService('liip_monitor.reporter.swift_mailer');
    }

    public function testProcessSwiftMailer()
    {
        $this->setParameter('liip_monitor.mailer.enabled', true);
        $this->setDefinition('mailer', new Definition(\Swift_Mailer::class));

        $this->compile();

        $this->assertContainerBuilderHasService('liip_monitor.reporter.swift_mailer');
        $this->assertContainerBuilderNotHasService('liip_monitor.reporter.symfony_mailer');
    }

    public function testSymfonyMailer()
    {
        $this->setParameter('liip_monitor.mailer.enabled', true);
        $this->setDefinition('mailer', new Definition(MailerInterface::class));

        $this->compile();

        $this->assertContainerBuilderHasService('liip_monitor.reporter.symfony_mailer');
        $this->assertContainerBuilderNotHasService('liip_monitor.reporter.swift_mailer');
    }

    public function testMailerWithoutPackage()
    {
        $this->setParameter('liip_monitor.mailer.enabled', true);
        $this->expectExceptionMessage('To enable mail reporting you have to install the "swiftmailer/swiftmailer" or "symfony/mailer".');
        $this->expectException(\InvalidArgumentException::class);

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MailerCompilerPass());
    }
}
