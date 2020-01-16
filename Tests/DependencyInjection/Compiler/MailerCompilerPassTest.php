<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection\Compiler;

use Liip\MonitorBundle\DependencyInjection\Compiler\MailerCompilerPass;
use Liip\MonitorBundle\Helper\SwiftMailerReporter;
use Liip\MonitorBundle\Helper\SymfonyMailerReporter;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Mailer\MailerInterface;

class MailerCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testDisabledMailer()
    {
        $this->setParameter('liip_monitor.mailer.enabled', false);

        $this->compile();

        $this->assertContainerBuilderNotHasService('liip_monitor.reporter.symfony_mailer');
        $this->assertContainerBuilderNotHasService('liip_monitor.reporter.swift_mailer');
    }

    public function testSwiftMailer()
    {
        $this->setParameter('liip_monitor.mailer.enabled', true);
        $this->setDefinition('mailer', new Definition(\Swift_Mailer::class));

        $this->compile();

        $this->assertContainerBuilderNotHasService('liip_monitor.reporter.symfony_mailer');
        $this->assertContainerBuilderHasService('liip_monitor.reporter.swift_mailer', SwiftMailerReporter::class);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'liip_monitor.reporter.swift_mailer',
            0,
            new Reference('mailer')
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'liip_monitor.reporter.swift_mailer',
            1,
            '%liip_monitor.mailer.recipient%'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'liip_monitor.reporter.swift_mailer',
            2,
            '%liip_monitor.mailer.sender%'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'liip_monitor.reporter.swift_mailer',
            3,
            '%liip_monitor.mailer.subject%'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'liip_monitor.reporter.swift_mailer',
            4,
            '%liip_monitor.mailer.send_on_warning%'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'liip_monitor.reporter.swift_mailer',
            'liip_monitor.additional_reporter',
            ['alias' => 'swift_mailer']
        );
    }

    public function testSwiftMailerWithAliasDefinition()
    {
        $this->setParameter('liip_monitor.mailer.enabled', true);
        $this->setDefinition('swift.mailer', new Definition(\Swift_Mailer::class));
        $this->container->setAlias('mailer', 'swift.mailer');

        $this->assertContainerBuilderHasAlias('mailer');

        $this->compile();

        $this->assertContainerBuilderHasService('liip_monitor.reporter.swift_mailer', SwiftMailerReporter::class);
    }

    public function testSymfonyMailer()
    {
        $this->setParameter('liip_monitor.mailer.enabled', true);
        $this->setDefinition('mailer', new Definition(MailerInterface::class));

        $this->compile();

        $this->assertContainerBuilderNotHasService('liip_monitor.reporter.swift_mailer');
        $this->assertContainerBuilderHasService('liip_monitor.reporter.symfony_mailer', SymfonyMailerReporter::class);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'liip_monitor.reporter.symfony_mailer',
            0,
            new Reference('mailer')
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'liip_monitor.reporter.symfony_mailer',
            1,
            '%liip_monitor.mailer.recipient%'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'liip_monitor.reporter.symfony_mailer',
            2,
            '%liip_monitor.mailer.sender%'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'liip_monitor.reporter.symfony_mailer',
            3,
            '%liip_monitor.mailer.subject%'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            'liip_monitor.reporter.symfony_mailer',
            4,
            '%liip_monitor.mailer.send_on_warning%'
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'liip_monitor.reporter.symfony_mailer',
            'liip_monitor.additional_reporter',
            ['alias' => 'symfony_mailer']
        );
    }

    public function testSymfonyMailerWithAliasDefinition()
    {
        $this->setParameter('liip_monitor.mailer.enabled', true);
        $this->setDefinition('symfony.mailer', new Definition(MailerInterface::class));
        $this->container->setAlias('mailer', 'symfony.mailer');

        $this->assertContainerBuilderHasAlias('mailer');

        $this->compile();

        $this->assertContainerBuilderHasService('liip_monitor.reporter.symfony_mailer', SymfonyMailerReporter::class);
    }

    public function testMailerWithoutPackage()
    {
        $this->setParameter('liip_monitor.mailer.enabled', true);
        $this->expectExceptionMessage('To enable mail reporting you have to install the "swiftmailer/swiftmailer" or "symfony/mailer".');
        $this->expectException(\InvalidArgumentException::class);

        $this->assertContainerBuilderNotHasService('mailer');
        $this->compile();
    }

    public function testMailerMissingAliasDefinition()
    {
        $this->setParameter('liip_monitor.mailer.enabled', true);
        $this->setDefinition('swift.mailer', new Definition(\Swift_Mailer::class));

        $this->assertFalse($this->container->hasAlias('mailer'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('To enable mail reporting you have to install the "swiftmailer/swiftmailer" or "symfony/mailer".');

        $this->compile();
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MailerCompilerPass());
    }
}
