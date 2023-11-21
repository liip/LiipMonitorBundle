<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Check\System;

use Liip\Monitor\Check\System\LoadAverageCheck;
use Liip\Monitor\Result\Status;
use Liip\Monitor\System\LinuxSystem;
use Liip\Monitor\Tests\CheckTests;
use Liip\Monitor\Tests\RequiresLinux;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class LoadAverageCheckTest extends TestCase
{
    use CheckTests, RequiresLinux;

    public static function checkResultProvider(): iterable
    {
        if (\sys_getloadavg()[0] < 0.99) {
            yield [
                LoadAverageCheck::oneMinute(new LinuxSystem(), 99, 100),
                Status::SUCCESS,
                '1 Minute System Load Average',
            ];

            yield [
                LoadAverageCheck::oneMinute(new LinuxSystem(), 1, 100),
                Status::WARNING,
            ];
        }

        yield [
            LoadAverageCheck::oneMinute(new LinuxSystem(), 1, 1),
            Status::FAILURE,
        ];

        if (\sys_getloadavg()[1] < 0.99) {
            yield [
                LoadAverageCheck::fiveMinute(new LinuxSystem(), 99, 100),
                Status::SUCCESS,
                '5 Minute System Load Average',
            ];

            yield [
                LoadAverageCheck::fiveMinute(new LinuxSystem(), 1, 100),
                Status::WARNING,
            ];
        }

        yield [
            LoadAverageCheck::fiveMinute(new LinuxSystem(), 1, 1),
            Status::FAILURE,
        ];

        if (\sys_getloadavg()[2] < 0.99) {
            yield [
                LoadAverageCheck::fifteenMinute(new LinuxSystem(), 99, 100),
                Status::SUCCESS,
                '15 Minute System Load Average',
            ];

            yield [
                LoadAverageCheck::fifteenMinute(new LinuxSystem(), 1, 100),
                Status::WARNING,
            ];
        }

        yield [
            LoadAverageCheck::fifteenMinute(new LinuxSystem(), 1, 1),
            Status::FAILURE,
        ];
    }
}
