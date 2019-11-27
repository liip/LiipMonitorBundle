<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection;

use Liip\MonitorBundle\DependencyInjection\Compiler\AddGroupsCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckCollectionTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\GroupRunnersCompilerPass;
use Liip\MonitorBundle\DependencyInjection\LiipMonitorExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class LiipMonitorExtensionTest extends AbstractExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $doctrineMock = $this->getMockBuilder('Doctrine\Common\Persistence\ConnectionRegistry')->getMock();
        $this->container->set('doctrine', $doctrineMock);
        $this->container->addCompilerPass(new AddGroupsCompilerPass());
        $this->container->addCompilerPass(new GroupRunnersCompilerPass());
        $this->container->addCompilerPass(new CheckTagCompilerPass());
        $this->container->addCompilerPass(new CheckCollectionTagCompilerPass());
    }

    /**
     * @dataProvider checkProvider
     */
    public function testChecksLoaded($name, $config, $checkClass, $checkAlias = null, $checkCount = 1)
    {
        // skip checks for missing classes
        if (!class_exists($checkClass)) {
            $this->setExpectedException('InvalidArgumentException');
        }

        if (!$checkAlias) {
            $checkAlias = $name;
        }

        $this->load(['checks' => [$name => $config]]);
        $this->compile();

        $runner = $this->container->get('liip_monitor.runner');

        $this->assertCount($checkCount, $runner->getChecks());
        $this->assertInstanceOf($checkClass, $runner->getCheck($checkAlias));
    }

    public function testDefaultNoChecks()
    {
        $this->load();
        $this->compile();

        $this->assertCount(0, $this->container->get('liip_monitor.runner')->getChecks());
    }

    public function testDefaultGroupParameterHasNoChecks()
    {
        $this->load();
        $this->compile();

        $this->assertTrue($this->container->hasParameter('liip_monitor.default_group'));
        $this->assertSame('default', $this->container->getParameter('liip_monitor.default_group'));
    }

    public function testDefaultGroupParameter()
    {
        $this->load(['checks' => ['php_extensions' => ['foo']]]);
        $this->compile();

        $this->assertTrue($this->container->hasParameter('liip_monitor.default_group'));
        $this->assertSame('default', $this->container->getParameter('liip_monitor.default_group'));
    }

    public function testDefaultGroupParameterCustom()
    {
        $this->load(['checks' => ['php_extensions' => ['foo']], 'default_group' => 'foo_bar']);
        $this->compile();

        $this->assertTrue($this->container->hasParameter('liip_monitor.default_group'));
        $this->assertSame('foo_bar', $this->container->getParameter('liip_monitor.default_group'));
    }

    public function testEnableController()
    {
        $this->load();

        $this->assertFalse($this->container->has('liip_monitor.health_controller'));

        $this->load(['enable_controller' => true]);

        $this->assertTrue($this->container->has('liip_monitor.health_controller'));
    }

    public function testSwiftMailer()
    {
        $this->container->setDefinition('mailer', new Definition(\Swift_Mailer::class));

        $this->load();

        $this->assertFalse(
            $this->container->has('liip_monitor.reporter.swift_mailer'),
            'Check that the mailer service is only loaded if required.'
        );

        $this->load(
            [
                'mailer' => [
                    'recipient' => 'foo@example.com',
                    'sender' => 'bar@example.com',
                    'subject' => 'Health Check',
                    'send_on_warning' => true,
                ],
            ]
        );

        $this->assertContainerBuilderHasService('liip_monitor.reporter.swift_mailer');
    }

    public function testSymfonyMailer()
    {
        $this->container->setDefinition('mailer', new Definition(MailerInterface::class));

        $this->load();

        $this->assertFalse(
            $this->container->has('liip_monitor.reporter.symfony_mailer'),
            'Check that the mailer service is only loaded if required.'
        );

        $this->load(
            [
                'mailer' => [
                    'recipient' => 'foo@example.com',
                    'sender' => 'bar@example.com',
                    'subject' => 'Health Check',
                    'send_on_warning' => true,
                ],
            ]
        );

        $this->assertContainerBuilderHasService('liip_monitor.reporter.symfony_mailer');
    }

    public function testMailerWithoutPackage()
    {
        $this->expectExceptionMessage('To enable mail reporting you have to install the "swiftmailer/swiftmailer" or "symfony/mailer".');
        $this->expectException(\InvalidArgumentException::class);

        $this->load(
            [
                'mailer' => [
                    'recipient' => 'foo@example.com',
                    'sender' => 'bar@example.com',
                    'subject' => 'Health Check',
                    'send_on_warning' => true,
                ],
            ]
        );
    }

    /**
     * @dataProvider mailerConfigProvider
     */
    public function testInvalidMailerConfig($config)
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->load($config);
    }

    public function mailerConfigProvider()
    {
        return [
            [
                [
                    'mailer' => [
                        'recipient' => 'foo@example.com',
                    ],
                ],
            ],
            [
                [
                    'mailer' => [
                        'recipient' => 'foo@example.com',
                        'sender' => 'bar@example.com',
                        'subject' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidCheckProvider
     */
    public function testInvalidExpressionConfig(array $config)
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->load(['checks' => ['expressions' => $config]]);
        $this->compile();
    }

    public function invalidCheckProvider()
    {
        return [
            [['foo']],
            [['foo' => ['critical_expression' => 'true']]],
            [['foo' => ['label' => 'foo']]],
        ];
    }

    public function checkProvider()
    {
        return [
            ['php_extensions', ['foo'], 'ZendDiagnostics\Check\ExtensionLoaded'],
            ['php_flags', ['foo' => 'true'], 'ZendDiagnostics\Check\PhpFlag', 'php_flag_foo'],
            ['php_version', ['5.3.3' => '='], 'ZendDiagnostics\Check\PhpVersion', 'php_version_5.3.3'],
            ['process_running', 'foo', 'ZendDiagnostics\Check\ProcessRunning', 'process_foo_running'],
            ['process_running', ['foo'], 'ZendDiagnostics\Check\ProcessRunning', 'process_foo_running'],
            ['process_running', ['foo', 'bar'], 'ZendDiagnostics\Check\ProcessRunning', 'process_foo_running', 2],
            ['process_running', ['foo', 'bar'], 'ZendDiagnostics\Check\ProcessRunning', 'process_bar_running', 2],
            ['readable_directory', ['foo'], 'ZendDiagnostics\Check\DirReadable'],
            ['writable_directory', ['foo'], 'ZendDiagnostics\Check\DirWritable'],
            ['class_exists', ['Foo'], 'ZendDiagnostics\Check\ClassExists'],
            ['cpu_performance', 0.5, 'ZendDiagnostics\Check\CpuPerformance'],
            ['disk_usage', ['path' => __DIR__], 'ZendDiagnostics\Check\DiskUsage'],
            ['symfony_requirements', ['file' => __DIR__.'/../../LiipMonitorBundle.php'], 'Liip\MonitorBundle\Check\SymfonyRequirements'],
            ['opcache_memory', null, 'ZendDiagnostics\Check\OpCacheMemory'],
            ['apc_memory', null, 'ZendDiagnostics\Check\ApcMemory'],
            ['apc_fragmentation', null, 'ZendDiagnostics\Check\ApcFragmentation'],
            ['doctrine_dbal', 'foo', 'Liip\MonitorBundle\Check\DoctrineDbal', 'doctrine_dbal_foo_connection'],
            ['doctrine_dbal', ['foo'], 'Liip\MonitorBundle\Check\DoctrineDbal', 'doctrine_dbal_foo_connection'],
            ['doctrine_dbal', ['foo', 'bar'], 'Liip\MonitorBundle\Check\DoctrineDbal', 'doctrine_dbal_foo_connection', 2],
            ['doctrine_dbal', ['foo', 'bar'], 'Liip\MonitorBundle\Check\DoctrineDbal', 'doctrine_dbal_bar_connection', 2],
            ['memcache', ['foo' => null], 'ZendDiagnostics\Check\Memcache', 'memcache_foo'],
            ['redis', ['foo' => null], 'ZendDiagnostics\Check\Redis', 'redis_foo'],
            ['http_service', ['foo' => null], 'ZendDiagnostics\Check\HttpService', 'http_service_foo'],
            ['guzzle_http_service', ['foo' => null], 'ZendDiagnostics\Check\GuzzleHttpService', 'guzzle_http_service_foo'],
            ['rabbit_mq', ['foo' => null], 'ZendDiagnostics\Check\RabbitMQ', 'rabbit_mq_foo'],
            ['symfony_version', null, 'Liip\MonitorBundle\Check\SymfonyVersion'],
            ['custom_error_pages', ['error_codes' => [500], 'path' => __DIR__, 'controller' => 'foo'], 'Liip\MonitorBundle\Check\CustomErrorPages'],
            ['security_advisory', ['lock_file' => __DIR__.'/../../composer.lock'], 'ZendDiagnostics\Check\SecurityAdvisory'],
            ['stream_wrapper_exists', ['foo'], 'ZendDiagnostics\Check\StreamWrapperExists'],
            ['file_ini', ['foo.ini'], 'ZendDiagnostics\Check\IniFile'],
            ['file_json', ['foo.json'], 'ZendDiagnostics\Check\JsonFile'],
            ['file_xml', ['foo.xml'], 'ZendDiagnostics\Check\XmlFile'],
            ['file_yaml', ['foo.yaml'], 'ZendDiagnostics\Check\YamlFile'],
            ['expressions', ['foo' => ['label' => 'foo', 'critical_expression' => 'true']], 'Liip\MonitorBundle\Check\Expression', 'expression_foo'],
            ['pdo_connections', ['foo' => ['dsn' => 'my-dsn']], 'ZendDiagnostics\Check\PDOCheck', 'pdo_foo'],
        ];
    }

    protected function getContainerExtensions(): array
    {
        return [new LiipMonitorExtension()];
    }
}
