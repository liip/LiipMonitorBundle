<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Tests\Fixture;

use Liip\Monitor\Check;
use Liip\Monitor\Result;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CheckService1 implements Check
{
    public function __toString(): string
    {
        return 'Check Service 1';
    }

    public function run(): Result
    {
        return Result::success();
    }
}
