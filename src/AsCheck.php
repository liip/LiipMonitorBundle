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
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsCheck
{
    public const DISABLE_CACHE = -1;

    /**
     * @param int|null        $ttl   Cache ttl in seconds or null to disable caching
     * @param string|string[] $suite The suite(s) this check should be part of
     * @param string|null     $label Override the label
     * @param string|null     $id    Override the ID
     */
    public function __construct(
        public readonly string|array $suite = [],
        public readonly ?int $ttl = null,
        public readonly ?string $label = null,
        public readonly ?string $id = null,
    ) {
    }
}
