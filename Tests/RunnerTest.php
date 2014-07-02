<?php

namespace Liip\MonitorBundle\Tests;

use Liip\MonitorBundle\Runner;
use ZendDiagnostics\Runner\Reporter\ReporterInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RunnerTest extends \PHPUnit_Framework_TestCase
{
    public function testAdditionalReporters()
    {
        $runner = new Runner();

        $this->assertCount(0, $runner->getReporters());

        $runner->addAdditionalReporter('foo', $this->createMockReporter());
        $runner->addAdditionalReporter('bar', $this->createMockReporter());

        $this->assertCount(0, $runner->getReporters());

        $runner->useAdditionalReporters(array('baz'));

        $this->assertCount(0, $runner->getReporters());

        $runner->useAdditionalReporters(array('foo'));

        $this->assertCount(1, $runner->getReporters());

        $runner->useAdditionalReporters(array('bar'));

        $this->assertCount(2, $runner->getReporters());

        $runner = new Runner();
        $runner->addAdditionalReporter('foo', $this->createMockReporter());
        $runner->addAdditionalReporter('bar', $this->createMockReporter());
        $runner->useAdditionalReporters(array('bar', 'foo'));

        $this->assertCount(2, $runner->getReporters());
    }

    /**
     * @return ReporterInterface
     */
    private function createMockReporter()
    {
        return $this->getMock('ZendDiagnostics\Runner\Reporter\ReporterInterface');
    }
}
