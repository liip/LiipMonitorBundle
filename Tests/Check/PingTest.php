<?php

namespace Liip\MonitorBundle\Tests\Check;

use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Liip\MonitorBundle\Check\Ping;

class PingTest extends \PHPUnit\Framework\TestCase
{
    public function testCheck(): void
    {
        $check = new Ping();
        $result = $check->check();
        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertInstanceOf(Success::class, $result);
        $this->assertSame('', $result->getMessage());
    }
}
