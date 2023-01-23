<?php

namespace Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use Liip\MonitorBundle\Check\SymfonyMessengerTransportCount;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;

class SymfonyMessengerTransportCountTest extends TestCase
{
    public function testLogicExceptionWhenCriticalLowerThenWarning()
    {
        $this->expectException(\LogicException::class);
        new SymfonyMessengerTransportCount(
            $this->getMockBuilder(MessageCountAwareInterface::class)->getMock(),
            'foo',
            ['warning_threshold' => 2, 'critical_threshold' => 1]
        );
    }

    /**
     * @dataProvider checkResultProvider
     */
    public function testCheckResult($config, $count, $expectedResult)
    {
        $transport = $this->getMockBuilder(MessageCountAwareInterface::class)->getMock();
        $transport->method('getMessageCount')->will($this->returnValue($count));

        $check = new SymfonyMessengerTransportCount(
            new Transport($count),
            'foo',
            $config
        );
        $this->assertInstanceOf($expectedResult, $check->check());
    }

    public function checkResultProvider()
    {
        return [
            [['warning_threshold' => null, 'critical_threshold' => 1], 0, Success::class],
            [['warning_threshold' => 0, 'critical_threshold' => 1], 0, Success::class],
            [['warning_threshold' => null, 'critical_threshold' => 0], 1, Failure::class],
            [['warning_threshold' => 10, 'critical_threshold' => 100], 9, Success::class],
            [['warning_threshold' => 10, 'critical_threshold' => 100], 10, Warning::class],
            [['warning_threshold' => 10, 'critical_threshold' => 100], 99, Warning::class],
            [['warning_threshold' => 10, 'critical_threshold' => 100], 100, Failure::class],
        ];
    }
}

class Transport implements MessageCountAwareInterface
{
    private $n;

    public function __construct(int $n)
    {
        $this->n = $n;
    }

    public function getMessageCount(): int
    {
        return $this->n;
    }
}
