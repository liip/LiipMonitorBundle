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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SwiftMailerReporterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider sendNoEmailProvider
     */
    public function testSendNoEmail(ResultInterface $result, $sendOnWarning): void
    {
        $mailer = $this->createMock('Swift_Mailer');
        $mailer
            ->expects(self::never())
            ->method('send');

        $results = new Collection();
        $results[$this->createMock(CheckInterface::class)] = $result;

        $reporter = new SwiftMailerReporter($mailer, 'foo@bar.com', 'bar@foo.com', 'foo bar', $sendOnWarning);
        $reporter->onFinish($results);
    }

    /**
     * @dataProvider sendEmailProvider
     */
    public function testSendEmail(ResultInterface $result, $sendOnWarning): void
    {
        $mailer = $this->createMock('Swift_Mailer');
        $mailer
            ->expects(self::once())
            ->method('send')
            ->with(self::isInstanceOf('Swift_Message'));

        $results = new Collection();
        $results[$this->createMock(CheckInterface::class)] = $result;

        $reporter = new SwiftMailerReporter($mailer, 'foo@bar.com', 'bar@foo.com', 'foo bar', $sendOnWarning);
        $reporter->onFinish($results);
    }

    public function sendEmailProvider(): array
    {
        return [
            [new Failure(), true],
            [new Warning(), true],
            [new Unknown(), true],
            [new Failure(), false],
        ];
    }

    public function sendNoEmailProvider(): array
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
