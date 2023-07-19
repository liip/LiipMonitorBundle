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

use Liip\Monitor\Check\Php\ApcuFragmentationCheck;
use Liip\Monitor\Info\Php\ApcuCacheInfo;
use Liip\Monitor\Result;
use Liip\Monitor\Result\Status;
use Liip\Monitor\Tests\CheckTests;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ApcuFragmentationCheckTest extends TestCase
{
    use CheckTests;

    public static function checkResultProvider(): iterable
    {
        if (!ApcuCacheInfo::isInstalled()) {
            return;
        }

        if (!ApcuCacheInfo::isEnabled()) {
            yield [
                new ApcuFragmentationCheck(70, 90),
                Result::skip('APCu is not enabled in the CLI environment'),
                'APCu Fragmentation',
            ];

            return;
        }

        yield [
            new ApcuFragmentationCheck(99, 100),
            Status::SUCCESS,
            'APCu Fragmentation',
        ];

        yield [
            new ApcuFragmentationCheck(0, 100),
            Status::WARNING,
        ];

        yield [
            new ApcuFragmentationCheck(99, 0),
            Status::FAILURE,
        ];
    }
}
