<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\System;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Webserver implements \Stringable
{
    public function __toString(): string
    {
        return $_SERVER['SERVER_SOFTWARE'] ?? 'n/a';
    }
}
