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

use Liip\Monitor\Check\System\FreeDiskSpaceCheck;
use Liip\Monitor\Result\Status;
use Liip\Monitor\System\LinuxSystem;
use Liip\Monitor\Tests\CheckTests;
use Liip\Monitor\Tests\RequiresLinux;
use PHPUnit\Framework\TestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FreeDiskSpaceCheckTest extends TestCase
{
    use CheckTests, RequiresLinux;

    public static function checkResultProvider(): iterable
    {
        yield [
            new FreeDiskSpaceCheck(new LinuxSystem(), '1mb', '10mb', '/'),
            Status::SUCCESS,
            'Free Disk Space (/)',
        ];

        yield [
            new FreeDiskSpaceCheck(new LinuxSystem(), '1tb', '10mb', '/'),
            Status::WARNING,
        ];

        yield [
            new FreeDiskSpaceCheck(new LinuxSystem(), '1mb', '1tb', '/'),
            Status::FAILURE,
        ];
    }
}
