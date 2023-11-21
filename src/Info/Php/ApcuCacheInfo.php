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

use Liip\Monitor\Utility\Percent;

/**
 * @source https://github.com/php/pecl-caching-apc/blob/master/apc.php
 *
 * @author Ralf Becker <beckerr@php.net>
 * @author Rasmus Lerdorf <rasmus@php.net>
 * @author Ilia Alshanetsky <ilia@prohost.org>
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ApcuCacheInfo extends CacheInfo
{
    private Percent $fragmented;

    public static function isInstalled(): bool
    {
        return \extension_loaded('apcu');
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
            throw new \RuntimeException('APCu is not installed.');
        }

        if (!\ini_get('apc.enabled')) {
            throw new \RuntimeException('APCu is not enabled.');
        }

        if ('cli' === \PHP_SAPI && !\ini_get('apc.enable_cli')) {
            throw new \RuntimeException('APCu is not enabled in the CLI environment.');
        }
    }

    public function percentFragmented(): Percent
    {
        if (isset($this->fragmented)) {
            return $this->fragmented;
        }

        self::ensureEnabled();

        if (!$info = \apcu_sma_info()) {
            throw new \RuntimeException('Unable to retrieve APCu shared memory information.');
        }

        $freeseg = $fragsize = $freetotal = 0;

        for ($i = 0; $i < $info['num_seg']; ++$i) {
            foreach ($info['block_lists'][$i] as $block) {
                /* Only consider blocks <5M for the fragmentation % */
                if ($block['size'] < 5 * 1024 * 1024) {
                    $fragsize += $block['size'];
                }

                $freetotal += $block['size'];
            }

            $freeseg += \count($info['block_lists'][$i]);
        }

        return $this->fragmented = Percent::fromDecimal($freeseg > 1 ? $fragsize / $freetotal : 0.0)->constrain();
    }

    public function refresh(): static
    {
        unset($this->fragmented);

        return parent::refresh();
    }

    protected function calculate(): array
    {
        self::ensureEnabled();

        if (!$apcuInfo = \apcu_cache_info()) {
            throw new \RuntimeException('Unable to retrieve APCu cache information.');
        }

        if (!$apcuMem = \apcu_sma_info()) {
            throw new \RuntimeException('Unable to retrieve APCu shared memory information.');
        }

        return [
            'usedMemory' => $apcuMem['num_seg'] * $apcuMem['seg_size'] - $apcuMem['avail_mem'],
            'freeMemory' => $apcuMem['avail_mem'],
            'hits' => $apcuInfo['num_hits'],
            'misses' => $apcuInfo['num_misses'],
        ];
    }
}
