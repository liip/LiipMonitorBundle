<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Check\Php;

use Liip\Monitor\Check\Php\OpCacheMemoryUsageCheck;
use Liip\Monitor\Info\Php\OpCacheInfo;
use Liip\Monitor\Result;
use Liip\Monitor\Result\Status;
use Liip\Monitor\Tests\CheckTests;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class OpCacheMemoryUsageCheckTest extends TestCase
{
    use CheckTests;

    public static function checkResultProvider(): iterable
    {
        if (!OpCacheInfo::isInstalled()) {
            return;
        }

        if (!OpCacheInfo::isEnabled()) {
            yield [
                new OpCacheMemoryUsageCheck(70, 90),
                Result::skip('OPcache is not enabled in the CLI environment'),
                'OPcache Memory Usage',
            ];

            return;
        }

        yield [
            new OpCacheMemoryUsageCheck(99, 100),
            Status::SUCCESS,
            'OPcache Memory Usage',
        ];

        yield [
            new OpCacheMemoryUsageCheck(0, 100),
            Status::WARNING,
        ];

        yield [
            new OpCacheMemoryUsageCheck(99, 0),
            Status::FAILURE,
        ];
    }
}
