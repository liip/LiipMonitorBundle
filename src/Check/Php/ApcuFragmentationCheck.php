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

use Liip\Monitor\Check\PercentThresholdCheck;
use Liip\Monitor\Info\Php\ApcuCacheInfo;
use Liip\Monitor\Result;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ApcuFragmentationCheck extends PercentThresholdCheck implements \Stringable
{
    public function __toString(): string
    {
        return 'APCu Fragmentation';
    }

    public static function configKey(): string
    {
        return 'apcu_fragmentation';
    }

    public static function configInfo(): ?string
    {
        return 'fails/warns if apcu fragmentation % is above thresholds';
    }

    public function run(): Result
    {
        if (ApcuCacheInfo::isInstalled() && 'cli' === \PHP_SAPI && !\ini_get('apc.enable_cli')) {
            return Result::skip('APCu is not enabled in the CLI environment');
        }

        $percentFragmented = (new ApcuCacheInfo())->percentFragmented();

        return $this->checkThresholds(
            value: $percentFragmented,
            summary: \sprintf('%s fragmented', $percentFragmented),
            detail: \sprintf('APCu cache is %s fragmented', $percentFragmented),
            context: [
                'percent_fragmented' => $percentFragmented,
            ]
        );
    }
}
