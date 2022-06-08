<?php

namespace Liip\MonitorBundle\Tests\Helper;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\Collection;
use Laminas\Diagnostics\Result\Failure;
use Liip\MonitorBundle\Helper\SymfonyMailerReporter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SymfonyMailerReporterTest extends TestCase
{
    /**
     * @var MockObject|MailerInterface
     */
    private $mailer;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
    }

    /**
     * @dataProvider getTestData
     */
    public function testSendMail(array $recipients, string $sender, string $subject): void
    {
        $reporter = new SymfonyMailerReporter($this->mailer, $recipients, $sender, $subject);

        $check = $this->createStub(CheckInterface::class);
        $check->method('getLabel')->willReturn('Some Label');

        $checks = new Collection();
        $checks[$check] = new Failure('Something goes wrong');

        $this->mailer
            ->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (Email $message) use ($recipients, $sender, $subject): bool {
                self::assertEquals(Address::createArray($recipients), $message->getTo(), 'Check if Recipient is sent correctly.');
                self::assertEquals([Address::create($sender)], $message->getFrom(), 'Check that the from header is set correctly.');
                self::assertSame($subject, $message->getSubject(), 'Check that the subject has been set.');
                self::assertSame('[Some Label] Something goes wrong', $message->getTextBody(), 'Check if the text body has been set.');

                return true;
            }));

        $reporter->onFinish($checks);
    }

    public static function getTestData(): iterable
    {
        return [
            [
                ['foo@bar.tld'],
                'test@foobar.tld',
                'Something went wrogin',
            ],
        ];
    }
}
