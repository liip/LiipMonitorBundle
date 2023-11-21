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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait RequiresLinux
{
    protected function setUp(): void
    {
        if ('Linux' !== \PHP_OS) {
            $this->markTestSkipped('Linux only test');
        }
    }
}
