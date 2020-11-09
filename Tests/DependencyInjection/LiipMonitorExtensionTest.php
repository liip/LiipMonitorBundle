<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection;

use Laminas\Diagnostics\Check\GuzzleHttpService;
use Laminas\Diagnostics\Check\HttpService;
use Laminas\Diagnostics\Check\Memcache;
use Laminas\Diagnostics\Check\Memcached;
use Laminas\Diagnostics\Check\PDOCheck;
use Laminas\Diagnostics\Check\PhpFlag;
use Laminas\Diagnostics\Check\PhpVersion;
use Laminas\Diagnostics\Check\ProcessRunning;
use Laminas\Diagnostics\Check\RabbitMQ;
use Laminas\Diagnostics\Check\Redis;
use Liip\MonitorBundle\Check\ApcFragmentation;
use Liip\MonitorBundle\Check\ApcMemory;
use Liip\MonitorBundle\Check\ClassExists;
use Liip\MonitorBundle\Check\CpuPerformance;
use Liip\MonitorBundle\Check\CustomErrorPages;
use Liip\MonitorBundle\Check\DiskUsage;
use Liip\MonitorBundle\Check\DoctrineDbal;
use Liip\MonitorBundle\Check\Expression;
use Liip\MonitorBundle\Check\IniFile;
use Liip\MonitorBundle\Check\JsonFile;
use Liip\MonitorBundle\Check\OpCacheMemory;
use Liip\MonitorBundle\Check\PhpExtension;
use Liip\MonitorBundle\Check\ReadableDirectory;
use Liip\MonitorBundle\Check\SecurityAdvisory;
use Liip\MonitorBundle\Check\StreamWrapperExists;
use Liip\MonitorBundle\Check\SymfonyVersion;
use Liip\MonitorBundle\Check\WritableDirectory;
use Liip\MonitorBundle\Check\XmlFile;
use Liip\MonitorBundle\Check\YamlFile;
use Liip\MonitorBundle\DependencyInjection\Compiler\AddGroupsCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckCollectionTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\GroupRunnersCompilerPass;
use Liip\MonitorBundle\DependencyInjection\LiipMonitorExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class LiipMonitorExtensionTest extends AbstractExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $doctrineMock = $this->getMockBuilder('Doctrine\Persistence\ConnectionRegistry')->getMock();
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

        $this->container->setParameter('kernel.project_dir', __DIR__);
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

    public function testDisabledDefaultMailer()
    {
        $this->load();

        $this->assertFalse($this->container->hasParameter('liip_monitor.mailer.enabled'));
        $this->assertFalse($this->container->hasParameter('liip_monitor.mailer.recipient'));
        $this->assertFalse($this->container->hasParameter('liip_monitor.mailer.sender'));
        $this->assertFalse($this->container->hasParameter('liip_monitor.mailer.subject'));
        $this->assertFalse($this->container->hasParameter('liip_monitor.mailer.send_on_warning'));
    }

    public function testDisabledMailer()
    {
        $this->load(
            [
                'mailer' => [
                    'enabled' => false,
                    'recipient' => 'foo@example.com',
                    'sender' => 'bar@example.com',
                    'subject' => 'Health Check',
                    'send_on_warning' => true,
                ],
            ]
        );

        $this->assertFalse($this->container->hasParameter('liip_monitor.mailer.enabled'));
        $this->assertFalse($this->container->hasParameter('liip_monitor.mailer.recipient'));
        $this->assertFalse($this->container->hasParameter('liip_monitor.mailer.sender'));
        $this->assertFalse($this->container->hasParameter('liip_monitor.mailer.subject'));
        $this->assertFalse($this->container->hasParameter('liip_monitor.mailer.send_on_warning'));
    }

    public function testEnabledMailer()
    {
        $this->load(
            [
                'mailer' => [
                    'enabled' => true,
                    'recipient' => 'foo@example.com',
                    'sender' => 'bar@example.com',
                    'subject' => 'Health Check',
                    'send_on_warning' => true,
                ],
            ]
        );

        $this->assertContainerBuilderHasParameter('liip_monitor.mailer.enabled', true);
        $this->assertContainerBuilderHasParameter('liip_monitor.mailer.recipient', ['foo@example.com']);
        $this->assertContainerBuilderHasParameter('liip_monitor.mailer.sender', 'bar@example.com');
        $this->assertContainerBuilderHasParameter('liip_monitor.mailer.subject', 'Health Check');
        $this->assertContainerBuilderHasParameter('liip_monitor.mailer.send_on_warning', true);
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
            ['php_extensions', ['foo', ['name' => 'bar', 'label' => 'baz']], PhpExtension::class, 'php_extension_foo', 2],
            ['php_extensions', ['foo', ['name' => 'bar', 'label' => 'baz']], PhpExtension::class, 'php_extension_bar', 2],
            ['php_extensions', [['name' => 'foo']], PhpExtension::class, 'php_extension_foo'],
            ['php_flags', ['foo' => 'true', 'bar' => ['value' => 'false', 'label' => 'baz']], PhpFlag::class, 'php_flag_foo', 2],
            ['php_flags', ['foo' => 'true', 'bar' => ['value' => 'false', 'label' => 'baz']], PhpFlag::class, 'php_flag_bar', 2],
            ['php_flags', ['foo' => ['value' => 'false']], PhpFlag::class, 'php_flag_foo'],
            ['php_version', ['5.3.3' => '=', '7.1.0' => ['operator' => '>=', 'label' => 'foo']], PhpVersion::class, 'php_version_5.3.3', 2],
            ['php_version', ['5.3.3' => '=', '7.1.0' => ['operator' => '>=', 'label' => 'foo']], PhpVersion::class, 'php_version_7.1.0', 2],
            ['php_version', ['7.1.0' => ['operator' => '>=']], PhpVersion::class, 'php_version_7.1.0'],
            ['process_running', 'foo', ProcessRunning::class, 'process_foo_running'],
            ['process_running', ['foo'], ProcessRunning::class, 'process_foo_running'],
            ['process_running', ['foo', ['name' => 'bar']], ProcessRunning::class, 'process_foo_running', 2],
            ['process_running', ['foo', ['name' => 'bar', 'label' => 'baz']], ProcessRunning::class, 'process_bar_running', 2],
            ['readable_directory', ['foo', ['path' => 'bar']], ReadableDirectory::class, 'readable_directory_foo', 2],
            ['readable_directory', ['foo', ['path' => 'bar', 'label' => 'baz']], ReadableDirectory::class, 'readable_directory_bar', 2],
            ['writable_directory', ['foo', ['path' => 'bar']], WritableDirectory::class, 'writable_directory_foo', 2],
            ['writable_directory', ['foo', ['path' => 'bar', 'label' => 'baz']], WritableDirectory::class, 'writable_directory_bar', 2],
            ['class_exists', ['Foo', ['name' => 'Bar']], ClassExists::class, 'class_exists_Foo', 2],
            ['class_exists', ['Foo', ['name' => 'Bar', 'label' => 'baz']], ClassExists::class, 'class_exists_Bar', 2],
            ['cpu_performance', 0.5, CpuPerformance::class],
            ['cpu_performance', ['performance' => 0.5, 'label' => 'foo'], CpuPerformance::class],
            ['disk_usage', ['path' => __DIR__], DiskUsage::class],
            ['disk_usage', ['path' => __DIR__, 'label' => 'foo'], DiskUsage::class],
            ['symfony_requirements', ['file' => __DIR__.'/../../LiipMonitorBundle.php', 'label' => 'foo'], 'Liip\MonitorBundle\Check\SymfonyRequirements'],
            ['opcache_memory', null, OpCacheMemory::class],
            ['opcache_memory', ['label' => 'foo'], OpCacheMemory::class],
            ['apc_memory', null, ApcMemory::class],
            ['apc_memory', ['label' => 'foo'], ApcMemory::class],
            ['apc_fragmentation', null, ApcFragmentation::class],
            ['apc_fragmentation', ['label' => 'foo'], ApcFragmentation::class],
            ['doctrine_dbal', 'foo', DoctrineDbal::class, 'doctrine_dbal_foo_connection'],
            ['doctrine_dbal', ['foo'], DoctrineDbal::class, 'doctrine_dbal_foo_connection'],
            ['doctrine_dbal', ['foo', 'bar'], DoctrineDbal::class, 'doctrine_dbal_foo_connection', 2],
            ['doctrine_dbal', ['foo', 'bar'], DoctrineDbal::class, 'doctrine_dbal_bar_connection', 2],
            ['memcache', ['foo' => null], Memcache::class, 'memcache_foo'],
            ['memcache', ['foo' => ['label' => 'bar']], Memcache::class, 'memcache_foo'],
            ['memcached', ['foo' => null], Memcached::class, 'memcached_foo'],
            ['memcached', ['foo' => ['label' => 'bar']], Memcached::class, 'memcached_foo'],
            ['redis', ['foo' => null], Redis::class, 'redis_foo'],
            ['redis', ['foo' => ['label' => 'bar']], Redis::class, 'redis_foo'],
            ['http_service', ['foo' => null], HttpService::class, 'http_service_foo'],
            ['http_service', ['foo' => ['label' => 'bar']], HttpService::class, 'http_service_foo'],
            ['guzzle_http_service', ['foo' => null], GuzzleHttpService::class, 'guzzle_http_service_foo'],
            ['guzzle_http_service', ['foo' => ['label' => 'bar']], GuzzleHttpService::class, 'guzzle_http_service_foo'],
            ['rabbit_mq', ['foo' => null], RabbitMQ::class, 'rabbit_mq_foo'],
            ['rabbit_mq', ['foo' => ['label' => 'bar']], RabbitMQ::class, 'rabbit_mq_foo'],
            ['symfony_version', null, SymfonyVersion::class],
            ['symfony_version', ['label' => 'foo'], SymfonyVersion::class],
            ['custom_error_pages', ['error_codes' => [500]], CustomErrorPages::class],
            ['custom_error_pages', ['error_codes' => [500], 'label' => 'foo'], CustomErrorPages::class],
            ['security_advisory', ['lock_file' => __DIR__.'/../../composer.lock'], SecurityAdvisory::class],
            ['security_advisory', ['lock_file' => __DIR__.'/../../composer.lock', 'label' => 'foo'], SecurityAdvisory::class],
            ['stream_wrapper_exists', ['foo'], StreamWrapperExists::class, 'stream_wrapper_exists_foo'],
            ['stream_wrapper_exists', ['foo', ['name' => 'bar', 'label' => 'baz']], StreamWrapperExists::class, 'stream_wrapper_exists_bar', 2],
            ['file_ini', ['foo.ini'], IniFile::class, 'file_ini_foo.ini'],
            ['file_ini', ['foo.ini', ['path' => 'bar.ini', 'label' => 'baz']], IniFile::class, 'file_ini_foo.ini', 2],
            ['file_json', ['foo.json'], JsonFile::class, 'file_json_foo.json'],
            ['file_json', ['foo.json', ['path' => 'bar.json', 'label' => 'baz']], JsonFile::class, 'file_json_foo.json', 2],
            ['file_xml', ['foo.xml'], XmlFile::class, 'file_xml_foo.xml'],
            ['file_xml', ['foo.xml', ['path' => 'bar.xml', 'label' => 'baz']], XmlFile::class, 'file_xml_foo.xml', 2],
            ['file_yaml', ['foo.yaml'], YamlFile::class, 'file_yaml_foo.yaml'],
            ['file_yaml', ['foo.yaml', ['path' => 'bar.yaml', 'label' => 'baz']], YamlFile::class, 'file_yaml_foo.yaml', 2],
            ['expressions', ['foo' => ['label' => 'foo', 'critical_expression' => 'true']], Expression::class, 'expression_foo'],
            ['pdo_connections', ['foo' => ['dsn' => 'my-dsn']], PDOCheck::class, 'pdo_foo'],
        ];
    }

    protected function getContainerExtensions(): array
    {
        return [new LiipMonitorExtension()];
    }
}
