<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection;

use Liip\MonitorBundle\DependencyInjection\Compiler\AddGroupsCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckCollectionTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\GroupRunnersCompilerPass;
use Liip\MonitorBundle\DependencyInjection\LiipMonitorExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class LiipMonitorExtensionTest extends AbstractExtensionTestCase
{
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

        $this->load(array('checks' => array($name => $config)));
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
        $this->load(array('checks' => array('php_extensions' => array('foo'))));
        $this->compile();

        $this->assertTrue($this->container->hasParameter('liip_monitor.default_group'));
        $this->assertSame('default', $this->container->getParameter('liip_monitor.default_group'));
    }

    public function testDefaultGroupParameterCustom()
    {
        $this->load(array('checks' => array('php_extensions' => array('foo')), 'default_group' => 'foo_bar'));
        $this->compile();

        $this->assertTrue($this->container->hasParameter('liip_monitor.default_group'));
        $this->assertSame('foo_bar', $this->container->getParameter('liip_monitor.default_group'));
    }

    public function testEnableController()
    {
        $this->load();

        $this->assertFalse($this->container->has('liip_monitor.health_controller'));

        $this->load(array('enable_controller' => true));

        $this->assertTrue($this->container->has('liip_monitor.health_controller'));
    }

    public function testMailer()
    {
        $this->load();

        $this->assertEquals(false, $this->container->has('liip_monitor.reporter.swift_mailer'));

        $this->load(
            array(
                'mailer' => array(
                    'recipient' => 'foo@example.com',
                    'sender' => 'bar@example.com',
                    'subject' => 'Health Check',
                    'send_on_warning' => true,
                ),
            )
        );

        $this->assertContainerBuilderHasService('liip_monitor.reporter.swift_mailer');
    }

    /**
     * @dataProvider mailerConfigProvider
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInvalidMailerConfig($config)
    {
        $this->load($config);
    }

    public function mailerConfigProvider()
    {
        return array(
            array(
                array(
                    'mailer' => array(
                        'recipient' => 'foo@example.com',
                    ),
                ),
            ),
            array(
                array(
                    'mailer' => array(
                        'recipient' => 'foo@example.com',
                        'sender' => 'bar@example.com',
                        'subject' => null,
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider invalidCheckProvider
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInvalidExpressionConfig(array $config)
    {
        $this->load(array('checks' => array('expressions' => $config)));
        $this->compile();
    }

    public function invalidCheckProvider()
    {
        return array(
            array(array('foo')),
            array(array('foo' => array('critical_expression' => 'true'))),
            array(array('foo' => array('label' => 'foo'))),
        );
    }

    public function checkProvider()
    {
        return array(
            array('php_extensions', array('foo'), 'ZendDiagnostics\Check\ExtensionLoaded'),
            array('php_flags', array('foo' => 'true'), 'ZendDiagnostics\Check\PhpFlag', 'php_flag_foo'),
            array('php_version', array('5.3.3' => '='), 'ZendDiagnostics\Check\PhpVersion', 'php_version_5.3.3'),
            array('process_running', 'foo', 'ZendDiagnostics\Check\ProcessRunning', 'process_foo_running'),
            array('process_running', array('foo'), 'ZendDiagnostics\Check\ProcessRunning', 'process_foo_running'),
            array('process_running', array('foo', 'bar'), 'ZendDiagnostics\Check\ProcessRunning', 'process_foo_running', 2),
            array('process_running', array('foo', 'bar'), 'ZendDiagnostics\Check\ProcessRunning', 'process_bar_running', 2),
            array('readable_directory', array('foo'), 'ZendDiagnostics\Check\DirReadable'),
            array('writable_directory', array('foo'), 'ZendDiagnostics\Check\DirWritable'),
            array('class_exists', array('Foo'), 'ZendDiagnostics\Check\ClassExists'),
            array('cpu_performance', 0.5, 'ZendDiagnostics\Check\CpuPerformance'),
            array('disk_usage', array('path' => __DIR__), 'ZendDiagnostics\Check\DiskUsage'),
            array('symfony_requirements', array('file' => __DIR__.'/../../LiipMonitorBundle.php'), 'Liip\MonitorBundle\Check\SymfonyRequirements'),
            array('opcache_memory', null, 'ZendDiagnostics\Check\OpCacheMemory'),
            array('apc_memory', null, 'ZendDiagnostics\Check\ApcMemory'),
            array('apc_fragmentation', null, 'ZendDiagnostics\Check\ApcFragmentation'),
            array('doctrine_dbal', 'foo', 'Liip\MonitorBundle\Check\DoctrineDbal', 'doctrine_dbal_foo_connection'),
            array('doctrine_dbal', array('foo'), 'Liip\MonitorBundle\Check\DoctrineDbal', 'doctrine_dbal_foo_connection'),
            array('doctrine_dbal', array('foo', 'bar'), 'Liip\MonitorBundle\Check\DoctrineDbal', 'doctrine_dbal_foo_connection', 2),
            array('doctrine_dbal', array('foo', 'bar'), 'Liip\MonitorBundle\Check\DoctrineDbal', 'doctrine_dbal_bar_connection', 2),
            array('memcache', array('foo' => null), 'ZendDiagnostics\Check\Memcache', 'memcache_foo'),
            array('redis', array('foo' => null), 'ZendDiagnostics\Check\Redis', 'redis_foo'),
            array('http_service', array('foo' => null), 'ZendDiagnostics\Check\HttpService', 'http_service_foo'),
            array('guzzle_http_service', array('foo' => null), 'ZendDiagnostics\Check\GuzzleHttpService', 'guzzle_http_service_foo'),
            array('rabbit_mq', array('foo' => null), 'ZendDiagnostics\Check\RabbitMQ', 'rabbit_mq_foo'),
            array('symfony_version', null, 'Liip\MonitorBundle\Check\SymfonyVersion'),
            array('custom_error_pages', array('error_codes' => array(500), 'path' => __DIR__, 'controller' => 'foo'), 'Liip\MonitorBundle\Check\CustomErrorPages'),
            array('security_advisory', array('lock_file' => __DIR__.'/../../composer.lock'), 'ZendDiagnostics\Check\SecurityAdvisory'),
            array('stream_wrapper_exists', array('foo'), 'ZendDiagnostics\Check\StreamWrapperExists'),
            array('file_ini', array('foo.ini'), 'ZendDiagnostics\Check\IniFile'),
            array('file_json', array('foo.json'), 'ZendDiagnostics\Check\JsonFile'),
            array('file_xml', array('foo.xml'), 'ZendDiagnostics\Check\XmlFile'),
            array('file_yaml', array('foo.yaml'), 'ZendDiagnostics\Check\YamlFile'),
            array('expressions', array('foo' => array('label' => 'foo', 'critical_expression' => 'true')), 'Liip\MonitorBundle\Check\Expression', 'expression_foo'),
            array('pdo_connections', array('foo' => array('dsn' => 'my-dsn')), 'ZendDiagnostics\Check\PDOCheck', 'pdo_foo'),
        );
    }

    protected function getContainerExtensions()
    {
        return array(new LiipMonitorExtension());
    }

    protected function compile()
    {
        $doctrineMock = $this->getMockBuilder('Doctrine\Common\Persistence\ConnectionRegistry')->getMock();
        $this->container->set('doctrine', $doctrineMock);
        $this->container->addCompilerPass(new AddGroupsCompilerPass());
        $this->container->addCompilerPass(new GroupRunnersCompilerPass());
        $this->container->addCompilerPass(new CheckTagCompilerPass());
        $this->container->addCompilerPass(new CheckCollectionTagCompilerPass());

        parent::compile();
    }
}
