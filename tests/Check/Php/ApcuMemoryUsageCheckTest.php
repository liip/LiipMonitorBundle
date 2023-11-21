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

use Liip\Monitor\Check\Php\ApcuMemoryUsageCheck;
use Liip\Monitor\Info\Php\ApcuCacheInfo;
use Liip\Monitor\Result;
use Liip\Monitor\Result\Status;
use Liip\Monitor\Tests\CheckTests;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ApcuMemoryUsageCheckTest extends TestCase
{
    use CheckTests;

    public static function checkResultProvider(): iterable
    {
        if (!ApcuCacheInfo::isInstalled()) {
            return;
        }

        if (!ApcuCacheInfo::isEnabled()) {
            yield [
                new ApcuMemoryUsageCheck(70, 90),
                Result::skip('APCu is not enabled in the CLI environment'),
                'APCu Memory Usage',
            ];

            return;
        }

        yield [
            new ApcuMemoryUsageCheck(99, 100),
            Status::SUCCESS,
            'APCu Memory Usage',
        ];

        yield [
            new ApcuMemoryUsageCheck(0, 100),
            Status::WARNING,
        ];

        yield [
            new ApcuMemoryUsageCheck(99, 0),
            Status::FAILURE,
        ];
    }
}
