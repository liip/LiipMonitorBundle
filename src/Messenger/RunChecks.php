<?php

/*
 * This file is part of the liip/monitor-bundle package.
 *
 * (c) Liip
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\Monitor\Messenger;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RunChecks extends RunMessage
{
    /**
     * @param string[] $ids The check IDs to run
     */
    public function __construct(
        public readonly array $ids,
        bool $cache = true,
    ) {
        parent::__construct($cache);
    }
}
