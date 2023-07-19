<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Check
{
    public function run(): Result;
}
