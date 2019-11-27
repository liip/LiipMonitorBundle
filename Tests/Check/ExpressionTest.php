<?php

namespace Liip\MonitorBundle\Tests\Check;

use Liip\MonitorBundle\Check\Expression;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ExpressionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider checkResultProvider
     */
    public function testCheckResult($warningCheck, $criticalCheck, $warningMessage, $criticalMessage, $expectedResultClass, $expectedMessage)
    {
        $check = new Expression('foo', $warningCheck, $criticalCheck, $warningMessage, $criticalMessage);
        $this->assertSame('foo', $check->getLabel());

        $result = $check->check();
        $this->assertInstanceOf('ZendDiagnostics\Result\ResultInterface', $result);
        $this->assertInstanceOf($expectedResultClass, $result);
        $this->assertSame($expectedMessage, $result->getMessage());
    }

    public function checkResultProvider()
    {
        return [
            ['true', 'true', null, null, 'ZendDiagnostics\Result\Success', ''],
            ['false', 'true', 'warning', 'fail', 'ZendDiagnostics\Result\Warning', 'warning'],
            ['true', 'false', 'warning', 'fail', 'ZendDiagnostics\Result\Failure', 'fail'],
            ['false', 'false', 'warning', 'fail', 'ZendDiagnostics\Result\Failure', 'fail'],
        ];
    }
}
