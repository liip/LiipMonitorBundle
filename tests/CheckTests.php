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

use Liip\Monitor\Check;
use Liip\Monitor\Check\CheckContext;
use Liip\Monitor\Result;
use Liip\Monitor\Result\Status;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait CheckTests
{
    #[Test]
    #[DataProvider('checkResultProvider')]
    public function run_check(Check|callable $check, Result|Status|callable $expectedResult, ?string $expectedLabel = null): void
    {
        if ($check instanceof \Closure) {
            $check = $check();
        }

        if ($expectedResult instanceof \Closure) {
            $expectedResult = $expectedResult();
        }

        $result = $check->run();

        if ($expectedResult instanceof Status) {
            $result = $result->status();
        }

        $this->assertEquals($expectedResult, $result);

        if ($expectedLabel) {
            $this->assertSame($expectedLabel, CheckContext::wrap($check)->label());
        }
    }

    /**
     * @return iterable<array{Check|callable():Check,Result|Status|callable():(Result|Status)}>
     */
    abstract public static function checkResultProvider(): iterable;
}
