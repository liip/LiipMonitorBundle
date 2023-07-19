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

use Liip\Monitor\Check\System\DiskUsageCheck;
use Liip\Monitor\Result\Status;
use Liip\Monitor\System\LinuxSystem;
use Liip\Monitor\Tests\CheckTests;
use Liip\Monitor\Tests\RequiresLinux;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DiskUsageCheckTest extends TestCase
{
    use CheckTests, RequiresLinux;

    public static function checkResultProvider(): iterable
    {
        yield [
            new DiskUsageCheck(new LinuxSystem(), 99, 100, '/'),
            Status::SUCCESS,
            'Disk Usage (/)',
        ];

        yield [
            new DiskUsageCheck(new LinuxSystem(), 1, 100, '/'),
            Status::WARNING,
        ];

        yield [
            new DiskUsageCheck(new LinuxSystem(), 100, 1, '/'),
            Status::FAILURE,
        ];
    }
}
