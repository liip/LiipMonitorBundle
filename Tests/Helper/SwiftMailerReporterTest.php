<?php

namespace Liip\MonitorBundle\Tests\Helper;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\AbstractResult;
use Laminas\Diagnostics\Result\Collection;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Skip;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use Liip\MonitorBundle\Helper\SwiftMailerReporter;
use Prophecy\Argument;

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
        $results[$this->prophesize(CheckInterface::class)->reveal()] = $result;

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
        $results[$this->prophesize(CheckInterface::class)->reveal()] = $result;

        $reporter = new SwiftMailerReporter($mailer->reveal(), 'foo@bar.com', 'bar@foo.com', 'foo bar', $sendOnWarning);
        $reporter->onFinish($results);
    }

    public function sendEmailProvider()
    {
        return [
            [new Failure(), true],
            [new Warning(), true],
            [new Unknown(), true],
            [new Failure(), false],
        ];
    }

    public function sendNoEmailProvider()
    {
        return [
            [new Success(), true],
            [new Skip(), true],
            [new Warning(), false],
        ];
    }
}

class Unknown extends AbstractResult
{
}
