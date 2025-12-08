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

use Liip\Monitor\Info\Php\ApcuCacheInfo;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait RequiresApcu
{
    /**
     * @beforeClass
     */
    public static function ensureApcuInstalled(): void
    {
        if (!ApcuCacheInfo::isInstalled()) {
            self::markTestSkipped('APCu is not installed');
        }
    }
}
