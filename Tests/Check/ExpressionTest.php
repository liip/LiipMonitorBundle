<?php

namespace Liip\MonitorBundle\Tests\Check;

use Liip\MonitorBundle\Check\Expression;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ExpressionTest extends \PHPUnit_Framework_TestCase
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
        return array(
            array('true', 'true', null, null, 'ZendDiagnostics\Result\Success', null),
            array('false', 'true', 'warning', 'fail', 'ZendDiagnostics\Result\Warning', 'warning'),
            array('true', 'false', 'warning', 'fail', 'ZendDiagnostics\Result\Failure', 'fail'),
            array('false', 'false', 'warning', 'fail', 'ZendDiagnostics\Result\Failure', 'fail'),
        );
    }
}
