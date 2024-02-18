<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Check;

use Liip\Monitor\Check\CallbackCheck;
use Liip\Monitor\Check\CheckRunner;
use Liip\Monitor\Result\Status;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CheckRunnerTest extends TestCase
{
    #[Test]
    public function exception_thrown(): void
    {
        $runner = new CheckRunner(
            $this->createMock(CacheInterface::class),
            $this->createMock(EventDispatcherInterface::class),
            new CallbackCheck('foo', fn() => throw new \Exception('foo')),
        );

        $result = $runner->run();

        $this->assertSame(Status::ERROR, $result->status());
        $this->assertSame(\Exception::class, $result->summary());
        $this->assertSame(\Exception::class.': foo', $result->detail());
        $this->assertSame(['exception', 'message', 'stack_trace'], \array_keys($result->context()));
        $this->assertInstanceOf(\Exception::class, $result->context()['exception']);
        $this->assertSame('foo', $result->context()['message']);
        $this->assertStringContainsString('Liip\Monitor\Tests\Check\CheckRunnerTest->Liip\Monitor\Tests\Check\{closure}()', $result->context()['stack_trace']);
    }

    #[Test]
    public function exception_thrown_with_previous(): void
    {
        $runner = new CheckRunner(
            $this->createMock(CacheInterface::class),
            $this->createMock(EventDispatcherInterface::class),
            new CallbackCheck('foo', function() {
                try {
                    self::exception();
                } catch (\Throwable $e) {
                    throw new \Exception('foo', previous: $e);
                }
            }),
        );

        $result = $runner->run();

        $this->assertSame(Status::ERROR, $result->status());
        $this->assertSame(\Exception::class, $result->summary());
        $this->assertSame(\Exception::class.': foo', $result->detail());
        $this->assertSame(['exception', 'message', 'stack_trace', 'previous', 'previous_message', 'previous_stack_trace'], \array_keys($result->context()));
        $this->assertInstanceOf(\Exception::class, $result->context()['exception']);
        $this->assertSame('foo', $result->context()['message']);
        $this->assertStringContainsString('Liip\Monitor\Tests\Check\CheckRunnerTest->Liip\Monitor\Tests\Check\{closure}()', $result->context()['stack_trace']);
        $this->assertInstanceOf(\RuntimeException::class, $result->context()['previous']);
        $this->assertSame('bar', $result->context()['previous_message']);
        $this->assertStringContainsString('Liip\Monitor\Tests\Check\CheckRunnerTest::exception()', $result->context()['previous_stack_trace']);
    }

    private static function exception(): void
    {
        throw new \RuntimeException('bar');
    }
}
