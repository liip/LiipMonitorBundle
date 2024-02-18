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

use Laminas\Diagnostics\Check\Callback;
use Laminas\Diagnostics\Result\AbstractResult;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Skip;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use Liip\Monitor\Check\LaminasDiagnosticsBridgeCheck;
use Liip\Monitor\Result;
use Liip\Monitor\Tests\CheckTests;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LaminasDiagnosticsBridgeCheckTest extends TestCase
{
    use CheckTests;

    public static function checkResultProvider(): iterable
    {
        yield [
            self::create(fn() => new Success()),
            Result::success(context: ['data' => null]),
            'Callback',
        ];

        yield [
            self::create(fn() => new Success('reason', 'foo'), 'Custom'),
            Result::success('reason', context: ['data' => 'foo']),
            'Custom',
        ];

        yield [
            self::create(fn() => new Warning('reason', 'foo')),
            Result::warning('reason', context: ['data' => 'foo']),
        ];

        yield [
            self::create(fn() => new Failure('reason', 'foo')),
            Result::failure('reason', context: ['data' => 'foo']),
        ];

        yield [
            self::create(fn() => new Skip('reason', 'foo')),
            Result::skip('reason', context: ['data' => 'foo']),
        ];

        yield [
            self::create(fn() => new class() extends AbstractResult {}),
            Result::unknown('', context: ['data' => null]),
        ];
    }

    #[Test]
    public function invalid(): void
    {
        $check = self::create(fn() => 'invalid');

        $this->expectException(\RuntimeException::class);

        $check->run();
    }

    private static function create(callable $callback, ?string $label = null): LaminasDiagnosticsBridgeCheck
    {
        return new LaminasDiagnosticsBridgeCheck(new Callback($callback), $label);
    }
}
