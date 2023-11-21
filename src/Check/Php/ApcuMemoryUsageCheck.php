<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Check\Php;

use Liip\Monitor\Check\StorageUsageCheck;
use Liip\Monitor\Info\Php\ApcuCacheInfo;
use Liip\Monitor\Info\StorageInfo;
use Liip\Monitor\Result;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ApcuMemoryUsageCheck extends StorageUsageCheck implements \Stringable
{
    public function __toString(): string
    {
        return 'APCu Memory Usage';
    }

    public static function configKey(): string
    {
        return 'apcu_memory_usage';
    }

    public static function configInfo(): ?string
    {
        return 'fails/warns if apcu memory usage % is above thresholds';
    }

    public function run(): Result
    {
        if (ApcuCacheInfo::isInstalled() && 'cli' === \PHP_SAPI && !\ini_get('apc.enable_cli')) {
            return Result::skip('APCu is not enabled in the CLI environment');
        }

        return parent::run();
    }

    protected function detail(StorageInfo $storage): string
    {
        return \sprintf('APCu memory is %s used (%s of %s total)', $storage->percentUsed(), $storage->used(), $storage->total());
    }

    protected function storage(): StorageInfo
    {
        return (new ApcuCacheInfo())->memory();
    }
}
