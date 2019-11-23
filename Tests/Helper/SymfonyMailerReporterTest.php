<?php

namespace Liip\MonitorBundle\Tests\Helper;

use Liip\MonitorBundle\Helper\SymfonyMailerReporter;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use ZendDiagnostics\Check\CheckInterface;
use ZendDiagnostics\Result\Collection;
use ZendDiagnostics\Result\Failure;

class SymfonyMailerReporterTest extends TestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy|MailerInterface
     */
    private $mailer;

    protected function setUp(): void
    {
        if (!interface_exists(MailerInterface::class)) {
            $this->markTestSkipped('Symfony Mailer not available.');
        }

        $this->mailer = $this->prophesize(MailerInterface::class);
    }

    /**
     * @dataProvider getTestData
     */
    public function testSendMail(array $recipients, string $sender, string $subject)
    {
        $reporter = new SymfonyMailerReporter($this->mailer->reveal(), $recipients, $sender, $subject);

        $check = $this->prophesize(CheckInterface::class);
        $check->getLabel()->willReturn('Some Label');

        $checks = new Collection();
        $checks[$check->reveal()] = new Failure('Something goes wrong');

        $this->mailer->send(Argument::that(function(Email $message) use ($recipients, $sender, $subject): bool {
            $this->assertEquals(Address::createArray($recipients), $message->getTo(), 'Check if Recipient is sent correctly.');
            $this->assertEquals([Address::create($sender)], $message->getFrom(), 'Check that the from header is set correctly.');
            $this->assertSame($subject, $message->getSubject(), 'Check that the subject has been set.');

            $this->assertSame('[Some Label] Something goes wrong', $message->getTextBody(), 'Check if the text body has been set.');

            return true;
        }))->shouldBeCalled();

        $reporter->onFinish($checks);
    }

    public static function getTestData(): iterable
    {
        return [
            [
                ['foo@bar.tld'],
                'test@foobar.tld',
                'Something went wrogin'
            ]
        ];
    }
}
