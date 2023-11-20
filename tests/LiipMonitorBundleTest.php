<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests;

use Liip\Monitor\Messenger\RunCheck;
use Liip\Monitor\Messenger\RunChecks;
use Liip\Monitor\Messenger\RunCheckSuite;
use Liip\Monitor\Result\ResultContext;
use Liip\Monitor\Result\ResultSet;
use Liip\Monitor\Result\Status;
use Liip\Monitor\Tests\Fixture\TestService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Zenstruck\Console\Test\InteractsWithConsole;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LiipMonitorBundleTest extends KernelTestCase
{
    use HandleTrait, InteractsWithConsole;

    /**
     * @test
     */
    public function services_are_autowired(): void
    {
        /** @var TestService $service */
        $service = self::getContainer()->get(TestService::class);

        $this->assertCount(19, $service->checks);
        $this->assertCount(3, $service->fooChecks);
        $this->assertCount(1, $service->barChecks);
        $this->assertCount(1, $service->bazChecks);

        $this->assertSame($service->system, $service->linuxSystem);
    }

    /**
     * @test
     */
    public function execute_list_command(): void
    {
        $this->executeConsoleCommand('monitor:list')
            ->assertSuccessful()
            ->assertOutputContains('Check Service 1')
            ->assertOutputContains('Check Service5')
            ->assertOutputContains('DBAL Connection "default"')
            ->assertOutputContains('DBAL Connection "another"')
        ;
    }

    /**
     * @test
     * @group slow
     */
    public function execute_health_command(): void
    {
        $this->executeConsoleCommand('monitor:health')
            ->assertOutputContains('19 check executed')
            ->assertOutputContains('OK DBAL Connection "default"')
            ->assertOutputContains('OK Check Service 1: Success')
        ;

        $this->executeConsoleCommand('monitor:health -v') // verbose
            ->assertOutputContains('19 check executed')
        ;
    }

    /**
     * @test
     * @group slow
     */
    public function messenger_run_check_suite(): void
    {
        $this->messageBus = self::getContainer()->get(MessageBusInterface::class);

        $results = $this->handle(new RunCheckSuite());

        $this->assertInstanceOf(ResultSet::class, $results);
        $this->assertCount(19, $results);
    }

    /**
     * @test
     */
    public function messenger_run_check(): void
    {
        $this->messageBus = self::getContainer()->get(MessageBusInterface::class);

        $result = $this->handle(new RunCheck('1786dade'));

        $this->assertInstanceOf(ResultContext::class, $result);
        $this->assertSame(Status::SUCCESS, $result->status());
    }

    /**
     * @test
     */
    public function messenger_run_checks(): void
    {
        $this->messageBus = self::getContainer()->get(MessageBusInterface::class);

        $results = $this->handle(new RunChecks(['769a89a9', '1786dade']));

        $this->assertInstanceOf(ResultSet::class, $results);
        $this->assertCount(2, $results);
    }
}
