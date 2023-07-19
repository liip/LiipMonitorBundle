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
use Liip\Monitor\Check\CheckContext;
use Liip\Monitor\Result;
use Liip\Monitor\Tests\CheckTests;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CheckContextTest extends TestCase
{
    use CheckTests;

    public static function checkResultProvider(): iterable
    {
        yield [
            new CheckContext(new CallbackCheck('name', fn() => null)),
            Result::success(),
        ];
    }

    /**
     * @test
     */
    public function id_can_be_overridden(): void
    {
        $this->assertSame('7e4c0e91', (new CheckContext(new CallbackCheck('name', fn() => null)))->id());
        $this->assertSame('override', (new CheckContext(new CallbackCheck('name', fn() => null), id: 'override'))->id());
    }

    /**
     * @test
     */
    public function wrapped_label_is_used_to_calculate_id(): void
    {
        $this->assertSame('7e4c0e91', (new CheckContext(new CallbackCheck('name', fn() => null)))->id());
        $this->assertSame('b22f5367', (new CheckContext(new CallbackCheck('name', fn() => null), label: 'override'))->id());
    }

    /**
     * @test
     */
    public function label_can_be_overridden(): void
    {
        $this->assertSame('name', (new CheckContext(new CallbackCheck('name', fn() => null)))->label());
        $this->assertSame('override', (new CheckContext(new CallbackCheck('name', fn() => null), label: 'override'))->label());
    }

    /**
     * @test
     */
    public function can_set_suites_and_ttl(): void
    {
        $context = new CheckContext(new CallbackCheck('name', fn() => null));

        $this->assertSame([], $context->suites());
        $this->assertNull($context->ttl());

        $context = new CheckContext(new CallbackCheck('name', fn() => null), ttl: 5, suite: 'foo');

        $this->assertSame(['foo'], $context->suites());
        $this->assertSame(5, $context->ttl());

        $this->assertSame(['foo', 'bar'], (new CheckContext(new CallbackCheck('name', fn() => null), suite: ['foo', 'bar']))->suites());
    }

    /**
     * @test
     */
    public function cannot_create_for_self(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CheckContext(new CheckContext(new CallbackCheck('name', fn() => null)));
    }
}
