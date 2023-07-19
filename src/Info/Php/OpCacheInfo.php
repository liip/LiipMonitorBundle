<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Info\Php;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class OpCacheInfo extends CacheInfo
{
    public static function isInstalled(): bool
    {
        return \function_exists('opcache_get_status');
    }

    public static function isEnabled(): bool
    {
        try {
            self::ensureEnabled();

            return true;
        } catch (\RuntimeException) {
            return false;
        }
    }

    public static function ensureEnabled(): void
    {
        if (!self::isInstalled()) {
            throw new \RuntimeException('OPCache is not installed.');
        }

        if (!\ini_get('opcache.enable')) {
            throw new \RuntimeException('OPCache is not enabled.');
        }

        if ('cli' === \PHP_SAPI && !\ini_get('opcache.enable_cli')) {
            throw new \RuntimeException('APCu is not enabled in the CLI environment.');
        }
    }

    protected function calculate(): array
    {
        self::ensureEnabled();

        if (!$opcache = \opcache_get_status(false)) {
            throw new \RuntimeException('Unable to retrieve opcache status.');
        }

        return [
            'usedMemory' => $opcache['memory_usage']['used_memory'] + $opcache['memory_usage']['wasted_memory'],
            'freeMemory' => $opcache['memory_usage']['free_memory'],
            'hits' => $opcache['opcache_statistics']['hits'],
            'misses' => $opcache['opcache_statistics']['misses'],
        ];
    }
}
