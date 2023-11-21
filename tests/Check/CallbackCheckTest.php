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
use Liip\Monitor\Result;
use Liip\Monitor\Tests\CheckTests;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CallbackCheckTest extends TestCase
{
    use CheckTests;

    public static function checkResultProvider(): iterable
    {
        yield [
            new CallbackCheck('Test', fn() => null),
            Result::success(),
            'Test',
        ];

        yield [
            new CallbackCheck('Test', fn() => true),
            Result::success(),
        ];

        yield [
            new CallbackCheck('Test', fn() => 'foo'),
            Result::failure('Unrecognized result returned from callback.'),
        ];

        yield [
            new CallbackCheck('Test', fn() => false),
            Result::failure('Fail'),
        ];

        yield [
            new CallbackCheck('Test', fn() => Result::unknown('foo')),
            Result::unknown('foo'),
        ];
    }
}
