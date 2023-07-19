<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Check;

use Liip\Monitor\Info\StorageInfo;
use Liip\Monitor\Result;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
abstract class StorageUsageCheck extends PercentThresholdCheck
{
    public function run(): Result
    {
        $storage = $this->storage();
        $percentUsed = $storage->percentUsed();

        return $this->checkThresholds(
            value: $percentUsed,
            summary: $percentUsed,
            detail: $this->detail($storage),
            context: [
                'percent_used' => $percentUsed->decimal(),
                'used' => $storage->used()->value(),
                'total' => $storage->total()->value(),
            ]
        );
    }

    abstract protected function detail(StorageInfo $storage): string;

    abstract protected function storage(): StorageInfo;
}
