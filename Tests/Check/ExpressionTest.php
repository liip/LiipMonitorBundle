<?php

namespace Liip\MonitorBundle\Tests\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use Liip\MonitorBundle\Check\Expression;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ExpressionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider checkResultProvider
     */
    public function testCheckResult($warningCheck, $criticalCheck, $warningMessage, $criticalMessage, $expectedResultClass, $expectedMessage): void
    {
        $check = new Expression('foo', $warningCheck, $criticalCheck, $warningMessage, $criticalMessage);
        $this->assertSame('foo', $check->getLabel());

        $result = $check->check();
        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertInstanceOf($expectedResultClass, $result);
        $this->assertSame($expectedMessage, $result->getMessage());
    }

    public function checkResultProvider(): array
    {
        return [
            ['true', 'true', null, null, Success::class, ''],
            ['false', 'true', 'warning', 'fail', Warning::class, 'warning'],
            ['true', 'false', 'warning', 'fail', Failure::class, 'fail'],
            ['false', 'false', 'warning', 'fail', Failure::class, 'fail'],
        ];
    }
}
