<?php

namespace Liip\MonitorBundle\Tests\Helper;

use Liip\MonitorBundle\Helper\SwiftMailerReporter;
use Prophecy\Argument;
use ZendDiagnostics\Result\Collection;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\ResultInterface;
use ZendDiagnostics\Result\Skip;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;
use ZendDiagnosticsTest\TestAsset\Result\Unknown;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SwiftMailerReporterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider sendNoEmailProvider
     */
    public function testSendNoEmail(ResultInterface $result, $sendOnWarning)
    {
        $mailer = $this->prophesize('Swift_Mailer');
        $mailer->send()->shouldNotBeCalled();

        $results = new Collection();
        $results[$this->prophesize('ZendDiagnostics\Check\CheckInterface')->reveal()] = $result;

        $reporter = new SwiftMailerReporter($mailer->reveal(), 'foo@bar.com', 'bar@foo.com', 'foo bar', $sendOnWarning);
        $reporter->onFinish($results);
    }

    /**
     * @dataProvider sendEmailProvider
     */
    public function testSendEmail(ResultInterface $result, $sendOnWarning)
    {
        $mailer = $this->prophesize('Swift_Mailer');
        $mailer->send(Argument::type('Swift_Message'))->shouldBeCalled();

        $results = new Collection();
        $results[$this->prophesize('ZendDiagnostics\Check\CheckInterface')->reveal()] = $result;

        $reporter = new SwiftMailerReporter($mailer->reveal(), 'foo@bar.com', 'bar@foo.com', 'foo bar', $sendOnWarning);
        $reporter->onFinish($results);
    }

    public function sendEmailProvider()
    {
        return array(
            array(new Failure(), true),
            array(new Warning(), true),
            array(new Unknown(), true),
            array(new Failure(), false),
        );
    }

    public function sendNoEmailProvider()
    {
        return array(
            array(new Success(), true),
            array(new Skip(), true),
            array(new Warning(), false),
        );
    }
}
