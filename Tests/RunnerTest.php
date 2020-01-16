<?php

namespace Liip\MonitorBundle\Tests;

use Laminas\Diagnostics\Runner\Reporter\ReporterInterface;
use Liip\MonitorBundle\Runner;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class RunnerTest extends \PHPUnit\Framework\TestCase
{
    public function testAdditionalReporters()
    {
        $runner = new Runner();

        $this->assertCount(0, $runner->getReporters());

        $runner->addAdditionalReporter('foo', $this->createMockReporter());
        $runner->addAdditionalReporter('bar', $this->createMockReporter());

        $this->assertCount(0, $runner->getReporters());

        $runner->useAdditionalReporters(['baz']);

        $this->assertCount(0, $runner->getReporters());

        $runner->useAdditionalReporters(['foo']);

        $this->assertCount(1, $runner->getReporters());

        $runner->useAdditionalReporters(['bar']);

        $this->assertCount(2, $runner->getReporters());

        $runner = new Runner();
        $runner->addAdditionalReporter('foo', $this->createMockReporter());
        $runner->addAdditionalReporter('bar', $this->createMockReporter());
        $runner->useAdditionalReporters(['bar', 'foo']);

        $this->assertCount(2, $runner->getReporters());
    }

    /**
     * @return ReporterInterface
     */
    private function createMockReporter()
    {
        return $this->getMockBuilder(ReporterInterface::class)->getMock();
    }
}
