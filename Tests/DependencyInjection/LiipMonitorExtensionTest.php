<?php

namespace Liip\MonitorBundle\Tests\DependencyInjection;

use Liip\MonitorBundle\DependencyInjection\Compiler\CheckCollectionTagCompilerPass;
use Liip\MonitorBundle\DependencyInjection\Compiler\CheckTagCompilerPass;
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

    public function testEnableController()
    {
        $this->load();

        $this->assertFalse($this->container->has('liip_monitor.health_controller'));

        $this->load(array('enable_controller' => true));

        $this->assertTrue($this->container->has('liip_monitor.health_controller'));
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
        );
    }

    protected function getContainerExtensions()
    {
        return array(new LiipMonitorExtension());
    }

    protected function compile()
    {
        $this->container->set('doctrine', $this->getMock('Doctrine\Common\Persistence\ConnectionRegistry'));
        $this->container->addCompilerPass(new CheckTagCompilerPass());
        $this->container->addCompilerPass(new CheckCollectionTagCompilerPass());

        parent::compile();
    }
}
