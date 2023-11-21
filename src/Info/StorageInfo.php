<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Info;

use Liip\Monitor\Utility\Percent;
use Zenstruck\Bytes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class StorageInfo
{
    public function __construct(private int $used, private int $total)
    {
    }

    public function total(): Bytes
    {
        return new Bytes($this->total);
    }

    public function free(): Bytes
    {
        return new Bytes($this->total - $this->used);
    }

    public function used(): Bytes
    {
        return new Bytes($this->used);
    }

    public function percentUsed(): Percent
    {
        return Percent::calculate($this->used, $this->total, divisionByZeroValue: 0)->constrain();
    }
}
